<?php

namespace FormGent\App\Setup;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Multisite\SiteLifecycle;

class Activation {
    public function __construct() {
        SiteLifecycle::install_current_site();
    }
}
