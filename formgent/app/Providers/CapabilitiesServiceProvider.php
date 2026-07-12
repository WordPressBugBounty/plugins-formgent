<?php

namespace FormGent\App\Providers;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Utils\Capabilities;
use FormGent\WpMVC\Contracts\Provider;

class CapabilitiesServiceProvider implements Provider {
    public function boot() {
        Capabilities::maybe_install();
        add_filter( 'user_has_cap', [Capabilities::class, 'filter_user_has_cap'] );
    }
}
