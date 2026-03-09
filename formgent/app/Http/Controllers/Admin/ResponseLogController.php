<?php

namespace FormGent\App\Http\Controllers\Admin;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\ResponseLogReadDTO;
use FormGent\App\Repositories\ResponseLogRepository;
use FormGent\WpMVC\Routing\Response;
use FormGent\App\Http\Controllers\Controller;
use FormGent\WpMVC\RequestValidator\Validator;
use WP_REST_Request;

class ResponseLogController extends Controller {
    public ResponseLogRepository $repository;

    public function __construct( ResponseLogRepository $repository ) {
        $this->repository = $repository;
    }

    public function index( Validator $validate, WP_REST_Request $request ) {
        $validate->validate(
            [
                'response_id' => 'required|numeric',
            ]
        );

        $per_page = min( 100, max( 1, intval( $request->get_param( 'per_page' ) ?: 10 ) ) );

        $dto = ( new ResponseLogReadDTO )
            ->set_response_id( intval( $request->get_param( 'response_id' ) ) )
            ->set_page( intval( $request->get_param( 'page' ) ?: 1 ) )
            ->set_per_page( $per_page );

        $result = $this->repository->get_paginated( $dto );

        return Response::send(
            array_merge(
                [ 'logs' => $result['logs'] ],
                $this->pagination( $request, $result['total'], $dto->get_per_page() )
            )
        );
    }

    public function delete( Validator $validate, WP_REST_Request $request ) {
        $validate->validate(
            [
                'id'          => 'required|numeric',
                'response_id' => 'required|numeric',
            ]
        );

        $this->repository->delete(
            intval( $request->get_param( 'response_id' ) ),
            intval( $request->get_param( 'id' ) )
        );

        return Response::send(
            [
                'message' => esc_html__( 'Log deleted successfully.', 'formgent' ),
            ]
        );
    }
}
