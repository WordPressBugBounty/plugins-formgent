<?php

namespace FormGent\App\Providers;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Http\Controllers\LoginController;
use FormGent\WpMVC\Contracts\Provider;

class LoginServiceProvider implements Provider {
    public function boot() {
        $controller = new LoginController();
        add_action( 'wp_ajax_formgent_login', [ $controller, 'login' ] );
        add_action( 'wp_ajax_nopriv_formgent_login', [ $controller, 'login' ] );
    }
}
