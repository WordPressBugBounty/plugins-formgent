<?php

namespace FormGent\App\Http\Controllers\Admin;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Models\Post;
use FormGent\App\Http\Controllers\Controller;
use FormGent\App\Repositories\SettingsRepository;
use FormGent\App\Services\Forms\FormPermalinkService;
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
        $settings = $this->repository->get();
        return Response::send( [ 'settings' => $settings ] );
    }

    public function update( Validator $validator, WP_REST_Request $request ) {
        $validator->validate(
            [
                'settings' => 'required|array'
            ]
        );

        $settings     = $request->get_param( 'settings' );
        $old_settings = $this->repository->get();
        $old_base     = FormPermalinkService::from_settings( $old_settings );

        if ( array_key_exists( FormPermalinkService::SETTING_KEY, $settings ) ) {
            $base = FormPermalinkService::validate( $settings[FormPermalinkService::SETTING_KEY] );

            if ( is_wp_error( $base ) ) {
                return Response::send( [ 'message' => $base->get_error_message() ], 422 );
            }

            $settings[FormPermalinkService::SETTING_KEY] = $base;
        }

        $payment = $settings['payment'] ?? $this->repository->get_by_key( 'payment', [] );

        if ( ! empty( $payment['status'] ) ) {
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
                $old_settings,
                $settings
            )
        );

        $new_base = FormPermalinkService::from_settings( array_merge( $old_settings, $settings ) );

        if ( $old_base !== $new_base ) {
            FormPermalinkService::mark_rewrite_rules_stale();
            do_action( 'formgent_form_permalink_base_changed', $old_base, $new_base );
        }

        $response = [
            'message'             => esc_html__( 'Settings have been saved successfully.', 'formgent' ),
            'redirect_url'        => false,
            'form_permalink_base' => $new_base,
        ];

        return Response::send( apply_filters( 'formgent_rest_settings_saved_response', $response, $request ) );
    }
}
