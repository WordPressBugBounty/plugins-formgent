<?php

namespace FormGent\App\Http\Middleware;

defined( "ABSPATH" ) || exit;

use WP_REST_Request;
use FormGent\WpMVC\Routing\Contracts\Middleware;

class Zapier implements Middleware {
    /**
    * Handle an incoming request.
    *
    * @param WP_REST_Request $request
    * @return bool
    */
    public function handle( WP_REST_Request $request ): bool {
        $settings_repo = formgent_settings_repository();

        if ( ! $settings_repo->get_by_key( "zapier_status" ) ) {
            return false;
        }

        $api_key       = (string) $settings_repo->get_by_key( 'zapier_token' );
        $authorization = trim( (string) $request->get_header( 'authorization' ) );
        $provided_key  = trim( (string) $request->get_header( 'x-formgent-key' ) );

        if ( 0 === stripos( $authorization, 'Bearer ' ) ) {
            $provided_key = trim( substr( $authorization, 7 ) );
        }

        if ( '' === $api_key || '' === $provided_key || ! hash_equals( $api_key, $provided_key ) ) {
            return false;
        }

        return true;
    }
}
