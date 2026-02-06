<?php

namespace FormGent\App\Http\Controllers\Admin;

defined( "ABSPATH" ) || exit;

use FormGent\App\DTO\OrderDTO;
use FormGent\App\EnumeratedList\OrderStatus;
use FormGent\App\Repositories\OrderRepository;
use FormGent\App\Http\Controllers\Controller;
use FormGent\WpMVC\Routing\Response;
use FormGent\WpMVC\RequestValidator\Validator;
use WP_REST_Request;

class OrderController extends Controller {
    public OrderRepository $repository;

    public function __construct( OrderRepository $repository ) {
        $this->repository = $repository;
    }

    public function order( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'response_id' => 'required|numeric',
            ]
        );

        $order = $this->repository->first_by_response_id( intval( $wp_rest_request->get_param( 'response_id' ) ), true );

        return Response::send(
            [
                'order' => $order
            ]
        );
    }

    public function update_status( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'id'     => 'required|numeric',
                'status' => 'required|string|accepted:' . OrderStatus::values(),
            ]
        );

        $id     = intval( $wp_rest_request->get_param( 'id' ) );
        $status = $wp_rest_request->get_param( 'status' );

        $dto = ( new OrderDTO )->set_id( $id )->set_status( $status );

        $this->repository->update( $dto );

        return Response::send(
            [
                'message' => esc_html__( 'Order status updated successfully', 'formgent' )
            ]
        );
    }
}