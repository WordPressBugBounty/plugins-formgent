<?php

namespace FormGent\App\Http\Controllers;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Http\Controllers\Controller;
use FormGent\App\Repositories\AnalyticRepository;
use FormGent\App\Repositories\FormRepository;

use FormGent\WpMVC\Routing\Response;
use FormGent\WpMVC\RequestValidator\Validator;

use WP_REST_Request;
use Exception;

class AnalyticsController extends Controller {
    public AnalyticRepository $analytic_repository;

    public FormRepository $form_repository;
    
    public function __construct( AnalyticRepository $analytic_repository, FormRepository $form_repository ) {
        $this->analytic_repository = $analytic_repository;
        $this->form_repository     = $form_repository;
    }

    public function increment_or_decrement_form_view_count( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'type' => 'string|accepted:+,-',
            ]
        );

        try {
            $form = $this->form_repository->get_by_id( absint( $wp_rest_request->get_param( 'id' ) ) );

            if ( ! $form ) {
                return Response::send(
                    [
                        'messages' => esc_html__( 'Form not found', 'formgent' )
                    ], 404
                );
            }

            return Response::send(
                [
                    'new_count' => $this->analytic_repository->update_form_view_count(
                        absint( $wp_rest_request->get_param( 'id' ) ), 
                        1,
                        $wp_rest_request->get_param( 'type' ) ?? '+'
                    )
                ]
            );
        } catch ( Exception $e ) {
            return Response::send(
                [
                    'messages' => esc_html__( 'Could\'t update the view count', 'formgent' )
                ], 422
            );
        }
    }
}