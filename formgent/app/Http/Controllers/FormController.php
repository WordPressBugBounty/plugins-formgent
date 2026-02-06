<?php

namespace FormGent\App\Http\Controllers;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Http\Controllers\Controller;
use FormGent\App\Repositories\FormRepository;
use FormGent\WpMVC\RequestValidator\Validator;
use FormGent\WpMVC\Routing\Response;
use WP_REST_Request;

class FormController extends Controller {
    public FormRepository $form_repository;

    public function __construct( FormRepository $form_repository ) {
        $this->form_repository = $form_repository;
    }

    public function show( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'id' => 'required|numeric'
            ]
        );

        $form = $this->form_repository->get_by_id( intval( $wp_rest_request->get_param( 'id' ) ) );

        if ( ! $form ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Form not found', 'formgent' )
                ], 404
            );
        }

        return Response::send(
            [
                'form' => $form
            ]
        );
    }
}