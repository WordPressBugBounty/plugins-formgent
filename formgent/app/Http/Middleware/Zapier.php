<?php

namespace FormGent\App\Http\Middleware;

defined( "ABSPATH" ) || exit;

use WP_REST_Request;
use FormGent\WpMVC\Routing\Contracts\Middleware;
use FormGent\WpMVC\Helpers\Helpers;
use FormGent\WpMVC\RequestValidator\Validator;

class Zapier implements Middleware {
    /**
    * Handle an incoming request.
    *
    * @param WP_REST_Request $request
    * @return bool
    */
    public function handle( WP_REST_Request $request ): bool {
        $request = Helpers::request();
        /**
         * @var Validator $validator
         */
        $validator                  = formgent_make( Validator::class );
        $validator->wp_rest_request = $request;
        $validator->validate(
            [
                'secret_key' => 'required|string|max:255'
            ], false
        );

        if ( $validator->is_fail() ) {
            return false;
        }

        $settings_repo = formgent_settings_repository();

        if ( ! $settings_repo->get_by_key( "zapier_status" ) ) {
            return false;
        }

        $api_key = $settings_repo->get_by_key( "zapier_token" );

        if ( $request->get_param( "secret_key" ) !== $api_key ) {
            return false;
        }

        return true;
    }
}