<?php

namespace FormGent\App\Http\Controllers\Admin;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Models\Post;
use FormGent\App\Http\Controllers\Controller;
use FormGent\App\Repositories\SettingsRepository;
use FormGent\WpMVC\RequestValidator\Validator;
use FormGent\WpMVC\Routing\Response;
use WP_REST_Request;

class SettingsController extends Controller
{
    public SettingsRepository $repository;

    /**
     * @param SettingsRepository $repository
     */
    public function __construct( SettingsRepository $repository ) {
        $this->repository = $repository;
    }

    public function index() {
        return Response::send(
            [
                'settings' => $this->repository->get(),
            ] 
        );
    }

    public function update( Validator $validator, WP_REST_Request $request ) {
        $validator->validate(
            [
                'settings' => 'required|array'
            ]
        );

        $settings = $request->get_param( 'settings' );
        $payment  = $settings['payment'];

        if ( $payment['status'] ) {
            if ( ! isset( $payment['success_page'] ) || ! Post::query()->select( '1' )->where( 'ID', intval( $payment['success_page'] ) )->where( 'post_status', 'publish' )->first() ) {
                $payment['success_page'] = wp_insert_post( [ 'post_title' => 'Payment Success', 'post_type' => 'page', 'post_status' => 'publish', 'post_content' => '<!-- wp:shortcode -->[formgent_payment_success]<!-- /wp:shortcode -->' ] );
            }

            if ( ! isset( $payment['failed_page'] ) || ! Post::query()->select( '1' )->where( 'ID', intval( $payment['failed_page'] ) )->where( 'post_status', 'publish' )->first() ) {
                $payment['failed_page'] = wp_insert_post( [ 'post_title' => 'Payment Failed', 'post_type' => 'page', 'post_status' => 'publish', 'post_content' => '<!-- wp:shortcode -->[formgent_payment_failed]<!-- /wp:shortcode -->' ] );
            }
        }

        $settings['payment'] = $payment;

        $this->repository->save(
            array_merge(
                $this->repository->get(), 
                $settings
            )
        );

        $response = [
            'message'      => esc_html__( 'Settings have been saved successfully.', 'formgent' ),
            'redirect_url' => false,
        ];

        return Response::send( apply_filters( 'formgent_rest_settings_saved_response', $response, $request ) );
    }
}
