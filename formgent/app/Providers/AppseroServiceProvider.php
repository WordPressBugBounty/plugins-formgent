<?php

namespace FormGent\App\Providers;

defined( "ABSPATH" ) || exit;

use FormGent\WpMVC\Contracts\Provider;

class AppseroServiceProvider implements Provider {
    public function boot() {
        $client = new \FormGent\Appsero\Client( '9d51731a-7531-4ef1-8acc-d3eea50e227a', 'FormGent', formgent_dir( 'formgent.php' ) );
        $client->insights()->init();
    }
}