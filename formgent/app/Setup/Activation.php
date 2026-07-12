<?php

namespace FormGent\App\Setup;

defined( 'ABSPATH' ) || exit;

use FormGent\Database\Setup;
use FormGent\App\Utils\Capabilities;

class Activation {
    public function __construct() {
        Capabilities::install();

        ( new Setup )->execute();

        /**
         * Adding formgent slug in wp rewrite rule.
         */
        register_post_type(
            'formgent_form', [
                'rewrite' => ['slug' => 'form']
            ]
        );
        flush_rewrite_rules( true );
    }
}
