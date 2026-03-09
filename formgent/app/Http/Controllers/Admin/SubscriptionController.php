<?php

namespace FormGent\App\Http\Controllers\Admin;

defined( "ABSPATH" ) || exit;

use FormGent\App\Http\Controllers\Controller;
use FormGent\App\Repositories\OrderRepository;
use FormGent\WpMVC\Routing\Response;
use FormGent\WpMVC\RequestValidator\Validator;
use WP_REST_Request;

class SubscriptionController extends Controller {
    public OrderRepository $repository;

    public function __construct( OrderRepository $repository ) {
        $this->repository = $repository;
    }

    public function details( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'response_id' => 'required|numeric',
                'order_id'    => 'required|numeric',
            ]
        );

        $response_id = intval( $wp_rest_request->get_param( 'response_id' ) );
        $order_id    = intval( $wp_rest_request->get_param( 'order_id' ) );

        $order = $this->repository->first_by_response_id( $response_id, true );

        if ( ! $order || (int) $order->id !== $order_id ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Order not found.', 'formgent' ),
                ],
                404
            );
        }

        $payment = $order->payment;

        if ( ! $payment ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Payment not found.', 'formgent' ),
                ],
                404
            );
        }

        $response_repo     = formgent_response_repository();
        $meta_key          = 'formgent_subscription_meta_' . $payment->id;
        $subscription_meta = $response_repo->get_meta_value( $response_id, $meta_key );

        if ( ! $subscription_meta ) {
            return Response::send(
                [
                    'is_subscription' => false,
                ]
            );
        }

        $subscription_meta = json_decode( $subscription_meta, true );

        if ( ! is_array( $subscription_meta ) ) {
            return Response::send(
                [
                    'is_subscription' => false,
                ]
            );
        }

        $gateway = $subscription_meta['gateway'] ?? $payment->method;

        // Build base details from stored meta.
        $details = [
            'is_subscription'   => true,
            'gateway'           => $gateway,
            'subscription_id'   => $payment->transaction_id ?? '',
            'plan_name'         => $subscription_meta['plan_name'] ?? '',
            'billing_interval'  => $subscription_meta['billing_interval'] ?? 'monthly',
            'status'            => 'active',
            'start_date'        => $payment->created_at ?? '',
            'next_billing_date' => '',
            'renewal_amount'    => (float) ( $order->final_amount ?? 0 ),
            'currency'          => $subscription_meta['currency'] ?? ( $payment->currency ?? 'USD' ),
            'trial_days'        => (int) ( $subscription_meta['trial_days'] ?? 0 ),
            'trial_end'         => null,
            'cancel_at'         => null,
        ];

        // Let pro enrich with live gateway data.
        $details = apply_filters(
            'formgent_admin_get_subscription_details',
            $details,
            $order,
            $payment,
            $subscription_meta
        );

        // Check for local cancel schedule.
        $cancel_meta_key = 'formgent_subscription_cancel_' . $payment->id;
        $cancel_meta     = $response_repo->get_meta_value( $response_id, $cancel_meta_key );

        if ( $cancel_meta ) {
            $cancel_data = json_decode( $cancel_meta, true );
            if ( is_array( $cancel_data ) && ! empty( $cancel_data['cancel_at'] ) ) {
                $details['cancel_at'] = $cancel_data['cancel_at'];
                if ( $details['status'] === 'active' ) {
                    $details['status'] = 'cancel_scheduled';
                }
            }
        }

        return Response::send( $details );
    }

    public function cancel( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'response_id' => 'required|numeric',
                'order_id'    => 'required|numeric',
            ]
        );

        $response_id = intval( $wp_rest_request->get_param( 'response_id' ) );
        $order_id    = intval( $wp_rest_request->get_param( 'order_id' ) );

        $order = $this->repository->first_by_response_id( $response_id, true );

        if ( ! $order || (int) $order->id !== $order_id ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Order not found.', 'formgent' ),
                ],
                404
            );
        }

        $payment = $order->payment;

        if ( ! $payment ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Payment not found.', 'formgent' ),
                ],
                404
            );
        }

        $response_repo     = formgent_response_repository();
        $meta_key          = 'formgent_subscription_meta_' . $payment->id;
        $subscription_meta = $response_repo->get_meta_value( $response_id, $meta_key );

        if ( ! $subscription_meta ) {
            return Response::send(
                [
                    'success' => false,
                    'message' => esc_html__( 'No subscription found for this order.', 'formgent' ),
                ],
                404
            );
        }

        $subscription_meta = json_decode( $subscription_meta, true );

        if ( ! is_array( $subscription_meta ) ) {
            return Response::send(
                [
                    'success' => false,
                    'message' => esc_html__( 'Invalid subscription metadata.', 'formgent' ),
                ],
                422
            );
        }

        $result = apply_filters(
            'formgent_admin_cancel_subscription',
            null,
            $order,
            $payment,
            $subscription_meta,
            [
                'response_id' => $response_id,
                'cancel_type' => 'period_end',
            ]
        );

        if ( $result === null ) {
            return Response::send(
                [
                    'success' => false,
                    'message' => esc_html__( 'Subscription cancellation is not supported for this gateway.', 'formgent' ),
                ],
                422
            );
        }

        return Response::send( $result );
    }
}
