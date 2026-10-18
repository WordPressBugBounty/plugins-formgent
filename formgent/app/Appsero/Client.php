<?php

namespace FormGent\App\Appsero;

defined( 'ABSPATH' ) || exit;

/**
 * FormGent Appsero client configuration.
 */
class Client extends \FormGent\Appsero\Client {
    /**
     * Get the FormGent analytics endpoint.
     *
     * @return string
     */
    public function endpoint() {
        $endpoint = apply_filters( 'appsero_endpoint', FORMGENT_APPSERO_ENDPOINT );

        return trailingslashit( $endpoint );
    }
}
