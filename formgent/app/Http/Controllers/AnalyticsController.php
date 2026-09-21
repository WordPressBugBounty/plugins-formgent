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
        try {
            $form_id = absint( $wp_rest_request->get_param( 'id' ) );
            $form    = $this->form_repository->get_by_id_publish( $form_id );

            if ( ! $form ) {
                return Response::send(
                    [
                        'messages' => esc_html__( 'Form not found', 'formgent' )
                    ], 404
                );
            }

            if ( ! formgent_is_form_analytics_enabled( $form->ID ) ) {
                return Response::send(
                    [
                        'code'    => 'formgent_analytics_disabled',
                        'message' => esc_html__( 'Analytics is disabled for this form.', 'formgent' ),
                    ],
                    403
                );
            }

            $remote_address = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
            $rate_key       = 'formgent_view_' . md5( $form_id . '|' . $remote_address );

            if ( get_transient( $rate_key ) ) {
                return Response::send( ['new_count' => absint( get_post_meta( $form_id, '_formgent_views', true ) )] );
            }

            set_transient( $rate_key, 1, MINUTE_IN_SECONDS );

            return Response::send(
                [
                    'new_count' => $this->analytic_repository->update_form_view_count(
                        $form_id,
                        1,
                        '+'
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
