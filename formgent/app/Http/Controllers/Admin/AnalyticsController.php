<?php

namespace FormGent\App\Http\Controllers\Admin;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Http\Controllers\Controller;
use FormGent\App\Repositories\AnalyticRepository;
use FormGent\App\Repositories\FormRepository;

use FormGent\WpMVC\Routing\Response;
use WP_REST_Request;

class AnalyticsController extends Controller {
    public AnalyticRepository $analytic_repository;

    public FormRepository $form_repository;
    
    public function __construct( AnalyticRepository $analytic_repository, FormRepository $form_repository ) {
        $this->analytic_repository = $analytic_repository;
        $this->form_repository     = $form_repository;
    }

    public function form_summary( WP_REST_Request $wp_rest_request ) {
        $form = $this->form_repository->get_by_id( absint( $wp_rest_request->get_param( 'id' ) ) );

        if ( ! $form ) {
            return Response::send(
                [
                    'messages' => esc_html__( 'Form not found', 'formgent' )
                ], 404
            );
        }

        $data = $this->analytic_repository->form_summary( $wp_rest_request->get_param( 'id' ) );
        $data = apply_filters( 'formgent_form_summary', $data, $wp_rest_request );

        return Response::send( [ 'data' => $data ] );
    }
}