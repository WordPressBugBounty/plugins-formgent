<?php

namespace FormGent\App\Http\Controllers;

defined( "ABSPATH" ) || exit;

use FormGent\App\Http\Controllers\Controller;
use FormGent\WpMVC\Exceptions\Exception;
use FormGent\WpMVC\RequestValidator\Validator;
use FormGent\WpMVC\Routing\Response;
use WP_REST_Request;

class CaptchaController extends Controller {
    private function verifyCaptcha( string $url, string $secret, WP_REST_Request $request ): array {
        $form_id        = absint( $request->get_param( 'form_id' ) );
        $response_token = (string) $request->get_param( 'response_token' );
        $form           = formgent_get_form_by_id( $form_id, true );

        if ( ! $form || ! formgent_get_response_by_token( $response_token, $form_id ) ) {
            return Response::send( ['message' => esc_html__( 'Form session not found.', 'formgent' )], 404 );
        }

        $response = wp_remote_post(
            $url, [
                'body' => [
                    'secret'   => $secret,
                    'response' => $request->get_param( 'response' ),
                    'remoteip' => formgent_get_user_ip_address(),
                ],
            ]
        );

        if ( is_wp_error( $response ) ) {
            //phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
            throw new Exception( $response->get_error_message() );
        }

        if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
            throw new Exception( esc_html__( "Failed to verify captcha", 'formgent' ), 500 );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( empty( $body['success'] ) ) {
            return Response::send(
                [
                    "message" => esc_html__( "Captcha verification failed", 'formgent' ),
                    'errors'  => (array) ( $body['error-codes'] ?? [] ),
                ],
                400
            );
        }

        $proof = wp_generate_password( 48, false, false );
        set_transient(
            'formgent_captcha_' . hash( 'sha256', $proof ),
            hash_hmac( 'sha256', $form_id . '|' . $response_token, wp_salt( 'nonce' ) ),
            10 * MINUTE_IN_SECONDS
        );

        return Response::send(
            [
                "message"       => esc_html__( "Captcha verification successful", 'formgent' ),
                'captcha_proof' => $proof,
            ]
        );
    }

    public function google( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate( ['response' => 'required|string', 'form_id' => 'required|numeric', 'response_token' => 'required|string'] );

        $settings_repository = formgent_settings_repository();
        $settings            = $settings_repository->get_by_key( "captcha_keys" );

        return $this->verifyCaptcha(
            'https://www.google.com/recaptcha/api/siteverify',
            $settings['recaptcha_secret_key'],
            $request
        );
    }

    public function hcaptcha( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate( ['response' => 'required|string', 'form_id' => 'required|numeric', 'response_token' => 'required|string'] );

        $settings_repository = formgent_settings_repository();
        $settings            = $settings_repository->get_by_key( "captcha_keys" );

        return $this->verifyCaptcha(
            'https://hcaptcha.com/siteverify',
            $settings['hcaptcha_secret_key'],
            $request
        );
    }

    public function turnstile( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate( ['response' => 'required|string', 'form_id' => 'required|numeric', 'response_token' => 'required|string'] );

        $settings_repository = formgent_settings_repository();
        $settings            = $settings_repository->get_by_key( "captcha_keys" );

        return $this->verifyCaptcha(
            'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            $settings['turnstile_secret_key'],
            $request
        );
    }
}
