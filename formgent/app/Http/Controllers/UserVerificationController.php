<?php

namespace FormGent\App\Http\Controllers;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Services\UserVerificationService;
use FormGent\WpMVC\RequestValidator\Validator;
use WP_REST_Request;

class UserVerificationController extends Controller {
    public function confirm( Validator $validator, WP_REST_Request $request ) {
        $validator->validate(
            [
                'user_id' => 'required|numeric',
                'token'   => 'required|string',
            ]
        );

        $user_id = absint( $request->get_param( 'user_id' ) );
        $token   = sanitize_text_field( (string) $request->get_param( 'token' ) );

        $service = formgent_singleton( UserVerificationService::class );
        $result  = $service->verify_user_by_token( $user_id, $token );

        if ( is_wp_error( $result ) ) {
            $code = 'formgent_expired_verification_link' === $result->get_error_code() ? 'expired' : 'invalid';
            wp_safe_redirect( $service->get_redirect_url( $code, $user_id ) );
            exit;
        }

        wp_safe_redirect( $service->get_redirect_url( 'success', $user_id ) );
        exit;
    }
}
