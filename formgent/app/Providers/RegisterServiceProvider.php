<?php

namespace FormGent\App\Providers;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Http\Controllers\RegisterController;
use FormGent\WpMVC\Contracts\Provider;

class RegisterServiceProvider implements Provider {
    public function boot() {
        $controller = new RegisterController();
        add_action( 'wp_ajax_formgent_register', [ $controller, 'register' ] );
        add_action( 'wp_ajax_nopriv_formgent_register', [ $controller, 'register' ] );
    }
}
