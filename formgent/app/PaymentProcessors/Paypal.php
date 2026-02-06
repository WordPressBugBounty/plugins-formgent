<?php

namespace FormGent\App\PaymentProcessors;

defined( "ABSPATH" ) || exit;

use WP_REST_Request;
use Exception;

use FormGent\App\Lib\Paypal\Paypal as PaymentProcessor;
use FormGent\App\Lib\Paypal\DTO\OrderDTO as PaypalOrderDTO;
use FormGent\App\Lib\Paypal\DTO\OrderPurchaseItemDTO;

use FormGent\App\DTO\PayDTO;
use FormGent\App\DTO\PaymentReturnDTO;
use FormGent\App\DTO\OrderItemDTO;

use FormGent\WpMVC\RequestValidator\Validator;
use FormGent\App\Contracts\PaymentInterface;

class Paypal implements PaymentInterface {
    protected PaymentProcessor $payment_processor;

    public function __construct() {
        $settings_repo    = formgent_settings_repository();
        $payment_settings = $settings_repo->get_by_key( 'payment', [] );

        $this->payment_processor = new PaymentProcessor( $payment_settings['paypal']['client_id'], $payment_settings['paypal']['secret_key'], $payment_settings['paypal']['test_mode'] );
    }

    public static function get_key(): string {
        return 'paypal';
    }

    public function pay( PayDTO $pay_dto ) {
        $return_url_args = [
            'order_id'   => $pay_dto->order->get_id(),
            'payment_id' => $pay_dto->payment->get_id(),
        ];

        $order_dto = ( new PaypalOrderDTO() )
            ->set_currency( $pay_dto->order->get_currency() )
            ->set_amount( $pay_dto->order->get_amount() )
            ->set_final_amount( $pay_dto->order->get_final_amount() )
            ->set_return_url( add_query_arg( $return_url_args, get_rest_url( null, '/formgent/payment/success/paypal' ) ) )
            ->set_cancel_url( add_query_arg( $return_url_args, get_rest_url( null, '/formgent/payment/cancel/paypal' ) ) );

        $purchase_items = [];

        foreach ( $pay_dto->get_order_items() as $item ) {
            /**
             * @var OrderItemDTO $item
             */
            $purchase_items[] = ( new OrderPurchaseItemDTO() )
                ->set_name( $item->get_title() )
                ->set_amount( $item->get_unit_amount() )
                ->set_quantity( $item->get_quantity() );
        }

        $order_dto->set_purchase_items( ...$purchase_items );

        $response = $this->payment_processor->create_order( $order_dto );

        if ( 'PAYER_ACTION_REQUIRED' !== $response['status'] ) {
            throw new Exception( 'Failed to create PayPal order' );
        }

        $payer_action_url = null;

        foreach ( $response['links'] as $link ) {
            if ( 'payer-action' === $link['rel'] ) {
                $payer_action_url = $link['href'];
                break;
            }
        }

        if ( ! $payer_action_url ) {
            throw new Exception( 'Failed to create PayPal order' );
        }

        return $payer_action_url;
    }

    public function capture( string $order_id ): array {
        return $this->payment_processor->capture_order( $order_id );
    }

    public function cancel( WP_REST_Request $request ): ?PaymentReturnDTO {
        $order_id   = $request->get_param( 'order_id' );
        $payment_id = $request->get_param( 'payment_id' );

        if ( empty( $order_id ) || empty( $payment_id ) ) {
            return null;
        }

        return ( new PaymentReturnDTO )
            ->set_order_id( $order_id )
            ->set_payment_id( $payment_id );
    }

    public function success( Validator $validator, WP_REST_Request $request ): PaymentReturnDTO {
        $validator->validate(
            [
                'token'      => 'required|string',
                'order_id'   => 'required|numeric',
                'payment_id' => 'required|numeric',
            ]
        );

        $token = $request->get_param( 'token' );

        try {
            $response = $this->payment_processor->capture_order( $token );

            if ( 'COMPLETED' !== $response['status'] ) {
                throw new Exception( '' );
            }

            return ( new PaymentReturnDTO )
                ->set_order_id( $request->get_param( 'order_id' ) )
                ->set_payment_id( $request->get_param( 'payment_id' ) )
                ->set_transaction_id( $token )
                ->set_billing_email( $response['payer']['email_address'] )
                ->set_billing_name( (string) $response['payer']['name']['given_name'] . ' ' . (string) $response['payer']['name']['surname'] )
                ->set_billing_country( $response['payer']['address']['country_code'] );
        } catch ( Exception $e ) {
            throw new Exception( 'Payment was not completed', 422 );
        }
    }
}
