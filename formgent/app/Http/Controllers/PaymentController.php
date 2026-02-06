<?php

namespace FormGent\App\Http\Controllers;

defined( "ABSPATH" ) || exit;

use FormGent\App\DTO\OrderItemDTO;
use FormGent\App\DTO\PayDTO;
use FormGent\App\DTO\OrderDTO;
use FormGent\App\DTO\PaymentDTO;
use FormGent\App\EnumeratedList\OrderStatus;
use FormGent\App\EnumeratedList\PaymentStatus;
use FormGent\App\Http\Controllers\Controller;
use FormGent\WpMVC\Routing\Response;
use FormGent\WpMVC\RequestValidator\Validator;
use WP_REST_Request;

class PaymentController extends Controller {
    public function success( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'payment_gateway' => 'required|string',
            ]
        );

        $payment_return_dto = formgent_payment_processor( $request->get_param( 'payment_gateway' ) )
            ->success( $validator, $request );

        // Update payment status to paid
        formgent_payment_repository()->update(
            ( new PaymentDTO )
                    ->set_id( $payment_return_dto->get_payment_id() )
                    ->set_transaction_id( $payment_return_dto->get_transaction_id() )
                    ->set_billing_email( $payment_return_dto->get_billing_email() )
                    ->set_billing_name( $payment_return_dto->get_billing_name() )
                    ->set_billing_country( $payment_return_dto->get_billing_country() )
                    ->set_status( PaymentStatus::PAID )
        );

        $order_repository = formgent_order_repository();
        // Update order status to paid
        $order_repository->update(
            ( new OrderDTO )->set_id( $payment_return_dto->get_order_id() )->set_status( OrderStatus::PAID ) 
        );
        
        $order = $order_repository->get_by_id( $payment_return_dto->get_order_id() );

        $settings = formgent_get_setting( 'payment' );

        if ( ! empty( $settings['success_page'] ) ) {
            $target_url = get_permalink( $settings['success_page'] );
        
            if ( ! empty( $target_url ) ) {
                $target_url = add_query_arg( 'order_id', $order->hash, $target_url );
                wp_safe_redirect( $target_url, 301 );
                exit;
            }
        }

        return Response::send(
            [
                "message" => esc_html__( "Payment successful.", 'formgent' )
            ]
        );
    }

    public function cancel( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'payment_gateway' => 'required|string',
            ]
        );

        $payment_return_dto = formgent_payment_processor( $request->get_param( 'payment_gateway' ) )->cancel( $request );

        if ( $payment_return_dto ) {
            // Update payment status to cancelled
            formgent_payment_repository()->update(
                ( new PaymentDTO )->set_id( $payment_return_dto->get_payment_id() )->set_status( PaymentStatus::CANCELLED )
            );

            // Update order status to cancelled
            formgent_order_repository()->update(
                ( new OrderDTO )->set_id( $payment_return_dto->get_order_id() )->set_status( OrderStatus::CANCELLED )
            );
        }

        $order_repository = formgent_order_repository();
        $order            = $order_repository->get_by_id( $payment_return_dto->get_order_id() );

        $settings = formgent_get_setting( 'payment' );

        if ( ! empty( $settings['failed_page'] ) ) {
            $target_url = get_permalink( $settings['failed_page'] );

            if ( ! empty( $target_url ) ) {
                $target_url = add_query_arg( 'order_id', $order->hash, $target_url );
                wp_safe_redirect( $target_url, 301 );
                exit;
            }
        }

        return Response::send(
            [
                "message" => esc_html__( "Payment canceled.", 'formgent' )
            ]
        );
    }

    public function retry( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'order_hash' => 'required|string',
            ]
        );
        
        $order_hash       = $request->get_param( 'order_hash' );
        $order_repository = formgent_order_repository();
        $order            = $order_repository->get_by( 'hash', $order_hash );

        if ( ! $order ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Order not found.', 'formgent' )
                ], 404
            );
        }

        if ( OrderStatus::PAID === $order->status ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Order is already paid.', 'formgent' )
                ], 400
            );
        }

        $order_item_repository = formgent_order_item_repository();
        $order_items           = $order_item_repository->get_by_order_id( $order->id );

        if ( empty( $order_items ) ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Order items not found.', 'formgent' )
                ], 404
            );
        }

        $payment_repository = formgent_payment_repository();
        $last_payment       = $payment_repository->get_by_order_id_last( $order->id );
        $payment_method     = $last_payment->method;
        
        $payment_gateways = formgent_get_payment_gateways();

        if ( ! isset( $payment_gateways[$payment_method] ) ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Payment method not found.', 'formgent' )
                ], 404
            );
        }

        $order_dto = ( new OrderDTO )
            ->set_id( $order->id )
            ->set_response_id( $order->response_id )
            ->set_amount( $order->amount )
            ->set_currency( $order->currency )
            ->set_final_amount( $order->final_amount )
            ->set_hash( $order->hash );

        $order_items_dto = [];

        foreach ( $order_items as $order_item ) {
            $order_items_dto[] = ( new OrderItemDTO )
                ->set_order_id( $order_dto->get_id() )
                ->set_title( $order_item->title )
                ->set_unit_amount( $order_item->unit_amount )
                ->set_quantity( $order_item->quantity )
                ->set_total_amount( $order_item->total_amount );
        }

        $payment_dto = ( new PaymentDTO )
            ->set_order_id( $order_dto->get_id() )
            ->set_amount( $last_payment->amount )
            ->set_currency( $last_payment->amount )
            ->set_method( $payment_method );

        $payment_dto->set_id( formgent_payment_repository()->create( $payment_dto ) );

        $pay_dto = ( new PayDTO )
            ->set_order( $order_dto )
            ->set_payment( $payment_dto )
            ->set_order_items( $order_items_dto );

        $payment_processor = formgent_payment_processor( $payment_method );
        $redirect_url      = $payment_processor->pay( $pay_dto );

        return Response::send(
            [
                'redirect_url' => $redirect_url
            ]
        );
    }
}