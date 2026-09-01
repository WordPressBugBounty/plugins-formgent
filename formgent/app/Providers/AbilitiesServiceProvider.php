<?php

namespace FormGent\App\Providers;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Abilities\AbilityRegistrar;
use FormGent\WpMVC\Contracts\Provider;

/**
 * Hooks FormGent into optional WordPress abilities and MCP lifecycles.
 */
class AbilitiesServiceProvider implements Provider {
    private AbilityRegistrar $registrar;

    public function __construct( AbilityRegistrar $registrar ) {
        $this->registrar = $registrar;
    }

    public function boot() {
        add_action( 'wp_abilities_api_categories_init', [$this->registrar, 'register_category'] );
        add_action( 'wp_abilities_api_init', [$this->registrar, 'register_abilities'] );
        add_action( 'mcp_adapter_init', [$this->registrar, 'register_server'] );
    }
}
