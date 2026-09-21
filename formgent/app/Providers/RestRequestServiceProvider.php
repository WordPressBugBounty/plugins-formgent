<?php

namespace FormGent\App\Providers;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\Contracts\Provider;
use FormGent\WpMVC\Routing\Providers\RouteServiceProvider;
use WP_REST_Request;

/** Bind the active REST request before route middleware is evaluated. */
class RestRequestServiceProvider implements Provider {
    public function boot() {
        add_filter( 'rest_pre_dispatch', [$this, 'bind_request'], 1, 3 );
    }

    /**
     * @param mixed $result Pre-dispatch result.
     * @return mixed
     */
    public function bind_request( $result, $server, WP_REST_Request $request ) {
        RouteServiceProvider::$container->set( WP_REST_Request::class, $request );

        return $result;
    }
}
