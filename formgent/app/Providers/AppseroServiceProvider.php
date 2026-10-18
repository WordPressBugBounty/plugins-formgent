<?php

namespace FormGent\App\Providers;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Appsero\Client;
use FormGent\WpMVC\Contracts\Provider;

class AppseroServiceProvider implements Provider {
    public function boot() {
        $project_hash = apply_filters( 'formgent_appsero_project_hash', FORMGENT_APPSERO_PROJECT_HASH );

        if ( ! is_string( $project_hash ) || '' === trim( $project_hash ) ) {
            return;
        }

        $client = new Client( $project_hash, 'FormGent', formgent_dir( 'formgent.php' ) );

        $client->set_textdomain( 'formgent' );
        $client->insights()->init();
    }
}
