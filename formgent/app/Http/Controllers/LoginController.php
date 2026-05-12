<?php

namespace FormGent\App\Http\Controllers;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Services\UserVerificationService;

class LoginController extends Controller {
    public function login() {
        check_ajax_referer( 'formgent_login_nonce', 'nonce' );

        $username = sanitize_text_field( (string) wp_unslash( $_POST['username'] ?? '' ) );
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Passwords must not be sanitized; they are passed as-is to wp_signon() which hashes them.
        $password = (string) wp_unslash( $_POST['password'] ?? '' );
        $remember = (bool) sanitize_text_field( wp_unslash( $_POST['remember'] ?? '' ) );

        if ( empty( $username ) || empty( $password ) ) {
            wp_send_json_error(
                [
                    'message' => esc_html__( 'Username/email and password are required.', 'formgent' ),
                ],
                422
            );
        }

        $credentials = [
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember,
        ];

        $user = wp_signon( $credentials, is_ssl() );

        if ( is_wp_error( $user ) ) {
            $message = formgent_singleton( UserVerificationService::class )->get_login_error_message( $user );
            wp_send_json_error(
                [
                    'message' => $message,
                ],
                in_array( $user->get_error_code(), [ 'formgent_registration_pending_email', 'formgent_registration_pending_manual' ], true ) ? 403 : 401
            );
        }

        wp_set_current_user( $user->ID );

        wp_send_json_success(
            [
                'message'      => esc_html__( 'Login successful. Redirecting...', 'formgent' ),
                'redirect_url' => apply_filters( 'formgent_login_redirect_url', home_url( '/' ), $user ),
            ]
        );
    }
}
