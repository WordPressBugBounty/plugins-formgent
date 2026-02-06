<?php

namespace FormGent\App\PaymentProcessors;

defined( "ABSPATH" ) || exit;

use FormGent\App\DTO\PayDTO;
use FormGent\App\DTO\PaymentReturnDTO;
use FormGent\Stripe\PaymentIntent;
use FormGent\WpMVC\RequestValidator\Validator;
use FormGent\App\Contracts\PaymentInterface;
use FormGent\Stripe\Checkout\Session;
use FormGent\Stripe\Stripe as StripeSDK;
use WP_REST_Request;

class Stripe implements PaymentInterface {
    public string $secret_key = '';

    public function __construct() {
        $settings_repo    = formgent_settings_repository();
        $payment_settings = $settings_repo->get_by_key( 'payment', [] );

        $this->secret_key = $payment_settings['stripe']['secret_key'];
    }
    
    public static function get_key(): string {
        return 'stripe';
    }

    public function pay( PayDTO $pay_dto ) {
        StripeSDK::setApiKey( $this->secret_key );

        $success_url = add_query_arg( [ 'session_id' => "{CHECKOUT_SESSION_ID}" ], get_rest_url( null, '/formgent/payment/success/stripe' ) );
        $cancel_url  = add_query_arg( [ 'order_id' => $pay_dto->order->get_id(), 'payment_id' => $pay_dto->payment->get_id(), ], get_rest_url( null, '/formgent/payment/cancel/stripe' ) );

        $line_items = [];

        foreach ( $pay_dto->order_items as $order_item ) {
            $line_items[] = [
                'price_data' => [
                    'currency'     => $pay_dto->order->get_currency(),
                    'product_data' => ['name' => $order_item->get_title()],
                    'unit_amount'  => $order_item->get_unit_amount() * 100,
                ],
                'quantity'   => $order_item->get_quantity(),
            ];
        }

        $session = Session::create(
            [
                'payment_method_types' => ['card'],
                'mode'                 => 'payment',
                'success_url'          => $success_url,
                'cancel_url'           => $cancel_url,
                'line_items'           => $line_items,
                'payment_intent_data'  => [
                    'metadata' => [
                        'order_id'    => $pay_dto->order->get_id(),
                        'payment_id'  => $pay_dto->payment->get_id(),
                        'response_id' => $pay_dto->order->get_response_id()
                    ]
                ]
            ],
        );

        return $session->url;
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
                'session_id' => 'required|string',
            ]
        );

        StripeSDK::setApiKey( $this->secret_key );

        $session_id = $request->get_param( 'session_id' );

        $session        = Session::retrieve( $session_id );
        $payment_intent = PaymentIntent::retrieve( $session->payment_intent );
        $transaction_id = $payment_intent->id;
        $meta_data      = $payment_intent->metadata;

        $dto = ( new PaymentReturnDTO )
        ->set_order_id( $meta_data->order_id )
        ->set_payment_id( $meta_data->payment_id )
        ->set_transaction_id( $transaction_id )
        ->set_billing_email( $session->customer_details->email )
        ->set_billing_name( $session->customer_details->name )
        ->set_billing_country( $session->customer_details->address->country );

        return $dto;
    }
}