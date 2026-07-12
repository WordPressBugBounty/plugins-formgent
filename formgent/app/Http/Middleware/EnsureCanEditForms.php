<?php

namespace FormGent\App\Http\Middleware;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Utils\Capabilities;
use FormGent\WpMVC\Routing\Contracts\Middleware;
use WP_REST_Request;

class EnsureCanEditForms implements Middleware {
    public function handle( WP_REST_Request $wp_rest_request ): bool {
        return Capabilities::can_edit_forms();
    }
}
