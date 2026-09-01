<?php

namespace FormGent\App\Services\Mcp;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Abilities\AccessGroup;
use FormGent\App\Repositories\McpSettingsRepository;
use WP_Error;

/**
 * Central execution-time gate and capability policy.
 */
class AbilityAccessService {
    private McpSettingsRepository $settings;

    public function __construct( McpSettingsRepository $settings ) {
        $this->settings = $settings;
    }

    /**
     * @param array<int,string> $groups Required access groups.
     * @param array<int,string> $capabilities Required WordPress capabilities.
     * @return true|WP_Error
     */
    public function authorize( array $groups, array $capabilities ) {
        $gate = $this->check_groups( $groups );

        if ( is_wp_error( $gate ) ) {
            return $gate;
        }

        if ( ! is_user_logged_in() ) {
            return McpErrorFactory::forbidden();
        }

        foreach ( array_unique( $capabilities ) as $capability ) {
            if ( '' === $capability || ! current_user_can( $capability ) ) {
                return McpErrorFactory::forbidden();
            }
        }

        return true;
    }

    /**
     * @param array<int,string> $groups Required access groups.
     * @return true|WP_Error
     */
    public function check_groups( array $groups ) {
        if ( ! $this->settings->master_enabled() ) {
            return McpErrorFactory::disabled();
        }

        foreach ( $groups as $group ) {
            if ( AccessGroup::MASTER === $group ) {
                continue;
            }

            $key = AccessGroup::setting_key( $group );

            if ( null === $key || ! $this->settings->enabled( $key ) ) {
                return McpErrorFactory::scope_disabled( $group );
            }
        }

        return true;
    }

    /**
     * Require FormGent access before the MCP transport initializes or lists tools.
     *
     * @param mixed $request Transport request supplied by the MCP Adapter.
     */
    public function transport_allowed( $request = null ): bool {
        return is_user_logged_in() && current_user_can( 'formgent_access' );
    }
}
