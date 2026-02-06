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
        $response = wp_remote_post(
            $url, [
                'body' => [
                    'secret'   => $secret,
                    'response' => $request->get_param( 'response' ),
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

        if ( ! $body['success'] ) {
            return Response::send(
                [
                    "message" => esc_html__( "Captcha verification failed", 'formgent' ),
                    'errors'  => $body['error-codes'],
                ],
                400
            );
        }

        return Response::send(
            [
                "message" => esc_html__( "Captcha verification successful", 'formgent' ),
            ]
        );
    }

    public function google( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate( ["response" => "required|string"] );

        $settings_repository = formgent_settings_repository();
        $settings            = $settings_repository->get_by_key( "captcha_keys" );

        return $this->verifyCaptcha(
            'https://www.google.com/recaptcha/api/siteverify',
            $settings['recaptcha_secret_key'],
            $request
        );
    }

    public function hcaptcha( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate( ["response" => "required|string"] );

        $settings_repository = formgent_settings_repository();
        $settings            = $settings_repository->get_by_key( "captcha_keys" );

        return $this->verifyCaptcha(
            'https://hcaptcha.com/siteverify',
            $settings['hcaptcha_secret_key'],
            $request
        );
    }

    public function turnstile( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate( ["response" => "required|string"] );

        $settings_repository = formgent_settings_repository();
        $settings            = $settings_repository->get_by_key( "captcha_keys" );

        return $this->verifyCaptcha(
            'https://challenges.cloudflare.com/turnstile/v0/siteverify',
            $settings['turnstile_secret_key'],
            $request
        );
    }
}