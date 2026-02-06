<?php

namespace FormGent\App\Providers;

defined( "ABSPATH" ) || exit;

use FormGent\WpMVC\Contracts\Provider;
use FormGent\WpMVC\Exceptions\Exception;

class DirectoristScriptProvider implements Provider {
    public function boot() {
        add_action( 'directorist_before_load_dashboard', [$this, 'enqueue_scripts'] );
    }

    public function enqueue_scripts() {
        wp_enqueue_script( 'formgent/directorist' );
    }
}