<?php

namespace FormGent\App\Http\Middleware;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\Routing\Contracts\Middleware;
use WP_REST_Request;

class EnsureIsUserAdmin implements Middleware
{
    /**
    * Handle an incoming request.
    *
    * @param  WP_REST_Request  $wp_rest_request
    * @return bool
    */
    public function handle( WP_REST_Request $wp_rest_request ): bool {
        return apply_filters( 'formgent_is_user_admin', current_user_can( 'manage_options' ) );
    }
}