<?php

namespace FormGent\App\PaymentProcessors;

defined( 'ABSPATH' ) || exit;

use Exception;
use FormGent\App\Contracts\PaymentInterface;
use FormGent\App\DTO\PayDTO;
use FormGent\App\DTO\PaymentDTO;
use FormGent\App\DTO\PaymentReturnDTO;
use FormGent\App\EnumeratedList\PaymentStatus;
use FormGent\Mollie\Api\Exceptions\ApiException;
use FormGent\Mollie\Api\MollieApiClient;
use FormGent\WpMVC\RequestValidator\Validator;
use WP_REST_Request;

class Mollie implements PaymentInterface {
    protected string $api_key = '';

    protected MollieApiClient $client;

    public function __construct() {
        $payment_settings = formgent_settings_repository()->get_by_key( 'payment', [] );

        $this->api_key = $payment_settings['mollie']['api_key'] ?? '';
        $this->client  = new MollieApiClient();

        if ( ! empty( $this->api_key ) ) {
            $this->client->setApiKey( $this->api_key );
        }
    }

    public static function get_key(): string {
        return 'mollie';
    }

    public function pay( PayDTO $pay_dto ) {
        if ( empty( $this->api_key ) ) {
            throw new Exception( __( 'Mollie API key is not configured.', 'formgent' ), 422 );
        }

        $callback_args = [
            'order_id'   => $pay_dto->order->get_id(),
            'payment_id' => $pay_dto->payment->get_id(),
        ];
        $payload       = [
            'amount'      => [
                'currency' => $pay_dto->order->get_currency(),
                'value'    => $this->format_amount( $pay_dto->order->get_final_amount() ),
            ],
            'description' => $this->build_description( $pay_dto ),
            'redirectUrl' => add_query_arg( $callback_args, get_rest_url( null, '/formgent/payment/success/mollie' ) ),
            'cancelUrl'   => add_query_arg( $callback_args, get_rest_url( null, '/formgent/payment/cancel/mollie' ) ),
            'metadata'    => [
                'order_id'    => (string) $pay_dto->order->get_id(),
                'payment_id'  => (string) $pay_dto->payment->get_id(),
                'response_id' => (string) $pay_dto->order->get_response_id(),
            ],
        ];
        $webhook_url   = $this->get_webhook_url();

        if ( ! empty( $webhook_url ) ) {
            $payload['webhookUrl'] = $webhook_url;
        }

        try {
            $payment = $this->client->payments->create( $payload );
        } catch ( ApiException $e ) {
            throw new Exception( $e->getMessage(), 422 );
        }

        formgent_payment_repository()->update(
            ( new PaymentDTO() )
                ->set_id( $pay_dto->payment->get_id() )
                ->set_transaction_id( $payment->id )
        );

        return $payment->getCheckoutUrl();
    }

    public function success( Validator $validator, WP_REST_Request $request ): PaymentReturnDTO {
        $validator->validate(
            [
                'order_id'   => 'required|numeric',
                'payment_id' => 'required|numeric',
            ]
        );

        $payment_id     = (int) $request->get_param( 'payment_id' );
        $order_id       = (int) $request->get_param( 'order_id' );
        $payment_record = $this->get_verified_payment_record( $order_id, $payment_id );
        $payment        = $this->fetch_payment( $payment_record->transaction_id );
        $status         = $this->map_status( $payment->status ) ?: PaymentStatus::PENDING;

        return $this->build_payment_return_dto(
            $order_id,
            $payment_id,
            $payment
        )->set_status( $status );
    }

    public function cancel( WP_REST_Request $request ): ?PaymentReturnDTO {
        $order_id   = $request->get_param( 'order_id' );
        $payment_id = $request->get_param( 'payment_id' );

        if ( empty( $order_id ) || empty( $payment_id ) ) {
            return null;
        }

        $order_id       = (int) $order_id;
        $payment_id     = (int) $payment_id;
        $payment_record = $this->get_verified_payment_record( $order_id, $payment_id );
        $payment        = $this->fetch_payment( $payment_record->transaction_id );
        $status         = $this->map_status( $payment->status ) ?: PaymentStatus::PENDING;

        return $this->build_payment_return_dto(
            $order_id,
            $payment_id,
            $payment
        )->set_status( $status );
    }

    public function handle_webhook( WP_REST_Request $request ): void {
        $mollie_payment_id = (string) $request->get_param( 'id' );

        if ( empty( $mollie_payment_id ) ) {
            return;
        }

        $payment_record = formgent_payment_repository()->get_by( 'transaction_id', $mollie_payment_id );

        if ( ! $payment_record ) {
            return;
        }

        if ( PaymentStatus::PAID === $payment_record->status ) {
            return;
        }

        $payment = $this->fetch_payment( $mollie_payment_id );

        if ( $payment->isPaid() ) {
            formgent_complete_payment(
                $this->build_payment_return_dto(
                    (int) $payment_record->order_id,
                    (int) $payment_record->id,
                    $payment
                )
            );

            return;
        }

        $status = $this->map_status( $payment->status );

        if ( null === $status ) {
            return;
        }

        formgent_update_payment_and_order_status(
            (int) $payment_record->order_id,
            (int) $payment_record->id,
            $status
        );
    }

    protected function get_verified_payment_record( int $order_id, int $payment_id ) {
        $payment_record = formgent_payment_repository()->get_by_id( $payment_id );

        if ( ! $payment_record ||
            (int) $payment_record->order_id !== $order_id ||
            self::get_key() !== $payment_record->method ||
            empty( $payment_record->transaction_id )
        ) {
            throw new Exception( __( 'Payment was not found.', 'formgent' ), 404 );
        }

        return $payment_record;
    }

    protected function fetch_payment( string $payment_id ) {
        if ( empty( $this->api_key ) ) {
            throw new Exception( __( 'Mollie API key is not configured.', 'formgent' ), 422 );
        }

        try {
            return $this->client->payments->get( $payment_id );
        } catch ( ApiException $e ) {
            throw new Exception( $e->getMessage(), 422 );
        }
    }

    protected function build_payment_return_dto( int $order_id, int $payment_id, $payment ): PaymentReturnDTO {
        return ( new PaymentReturnDTO() )
            ->set_order_id( $order_id )
            ->set_payment_id( $payment_id )
            ->set_transaction_id( $payment->id )
            ->set_billing_email( $this->extract_billing_email( $payment ) )
            ->set_billing_name( $this->extract_billing_name( $payment ) )
            ->set_billing_country( $this->extract_billing_country( $payment ) );
    }

    protected function extract_billing_email( $payment ): ?string {
        // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
        $billing_email = $payment->billingEmail ?? null;

        return is_string( $billing_email )
            ? $billing_email
            : null;
    }

    protected function extract_billing_name( $payment ): ?string {
        if ( isset( $payment->details->consumerName ) && is_string( $payment->details->consumerName ) ) {
            return $payment->details->consumerName;
        }

        return null;
    }

    protected function extract_billing_country( $payment ): ?string {
        // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
        $country_code = $payment->countryCode ?? null;

        if ( is_string( $country_code ) ) {
            return $country_code;
        }

        // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
        if ( isset( $payment->details->countryCode ) && is_string( $payment->details->countryCode ) ) {
            // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
            return $payment->details->countryCode;
        }

        return null;
    }

    protected function map_status( string $status ): ?string {
        switch ( $status ) {
            case 'paid':
                return PaymentStatus::PAID;
            case 'open':
            case 'pending':
            case 'authorized':
            case 'created':
                return PaymentStatus::PENDING;
            case 'failed':
                return PaymentStatus::FAILED;
            case 'expired':
                return PaymentStatus::EXPIRED;
            case 'canceled':
                return PaymentStatus::CANCELLED;
            default:
                return null;
        }
    }

    protected function build_description( PayDTO $pay_dto ): string {
        return sprintf(
            /* translators: %d: order id */
            __( 'FormGent Order #%d', 'formgent' ),
            $pay_dto->order->get_id()
        );
    }

    protected function get_webhook_url(): string {
        $webhook_url = (string) apply_filters(
            'formgent_mollie_webhook_url',
            get_rest_url( null, '/formgent/payment/webhook/mollie' )
        );

        if ( empty( $webhook_url ) || ! $this->is_public_webhook_url( $webhook_url ) ) {
            return '';
        }

        return $webhook_url;
    }

    protected function is_public_webhook_url( string $url ): bool {
        $parts = wp_parse_url( $url );
        $host  = $parts['host'] ?? '';

        if ( empty( $host ) || ! in_array( $parts['scheme'] ?? '', ['https', 'http'], true ) ) {
            return false;
        }

        if ( in_array( $host, ['localhost', '127.0.0.1', '::1'], true ) ) {
            return false;
        }

        foreach ( ['.local', '.test', '.invalid', '.example'] as $suffix ) {
            if ( substr( $host, -strlen( $suffix ) ) === $suffix ) {
                return false;
            }
        }

        if ( filter_var( $host, FILTER_VALIDATE_IP ) ) {
            if ( ! filter_var( $host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
                return false;
            }
        }

        return true;
    }

    protected function format_amount( float $amount ): string {
        return number_format( $amount, 2, '.', '' );
    }
}
