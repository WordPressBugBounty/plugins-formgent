<?php

namespace FormGent\App\Services\Mcp;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Repositories\McpSettingsRepository;
use FormGent\App\Abilities\AbilityRegistrar;

/**
 * Reports optional WordPress/MCP Adapter dependencies without loading them early.
 */
class McpStatusService {
    public const ADAPTER_PLUGIN      = 'mcp-adapter/mcp-adapter.php';
    public const MIN_ADAPTER_VERSION = '0.5.0';

    private McpSettingsRepository $settings;

    private ClientConfigService $client_config;

    public function __construct( McpSettingsRepository $settings, ClientConfigService $client_config ) {
        $this->settings      = $settings;
        $this->client_config = $client_config;
    }

    /**
     * @return array<string,mixed>
     */
    public function get(): array {
        $settings           = $this->settings->get();
        $abilities          = function_exists( 'wp_register_ability' );
        $adapter_installed  = $this->adapter_installed();
        $adapter_active     = $this->adapter_active();
        $adapter_version    = $this->adapter_version();
        $transport          = $this->transport_class();
        $adapter_compatible = $adapter_active && null !== $transport && ( '' === $adapter_version || version_compare( $adapter_version, self::MIN_ADAPTER_VERSION, '>=' ) );
        $endpoint           = rest_url( 'formgent/v1/mcp' );
        $enabled_tools      = $this->enabled_tool_count();
        $server_error       = AbilityRegistrar::get_server_error();
        $server_active      = $abilities
            && $adapter_compatible
            && $settings['enabled']
            && $settings['server']
            && AbilityRegistrar::is_server_registered()
            && null === $server_error
            && 0 < $enabled_tools;
        $state              = $this->state( $abilities, $adapter_installed, $adapter_active, $adapter_compatible, $server_active, $settings, $server_error );
        $https              = 0 === strpos( $endpoint, 'https://' );

        return [
            'settings' => $settings,
            'status'   => [
                'abilities_available'      => $abilities,
                'minimum_wordpress'        => '6.9',
                'adapter_installed'        => $adapter_installed,
                'adapter_active'           => $adapter_active,
                'adapter_compatible'       => $adapter_compatible,
                'adapter_version'          => $adapter_version,
                'minimum_adapter'          => self::MIN_ADAPTER_VERSION,
                'transport'                => $transport,
                'server_active'            => $server_active,
                'state'                    => $state,
                'endpoint'                 => $server_active ? $endpoint : '',
                'https'                    => $https,
                'https_warning'            => ! $https && ! $this->is_local_url( $endpoint ),
                'application_password_url' => admin_url( 'profile.php#application-passwords-section' ),
                'adapter_install_url'      => 'https://github.com/WordPress/mcp-adapter/releases/latest',
                'enabled_tool_count'       => $enabled_tools,
            ],
            'clients'  => $server_active ? $this->client_config->get( $endpoint ) : [],
        ];
    }

    public function transport_class(): ?string {
        if ( class_exists( '\\WP\\MCP\\Transport\\HttpTransport' ) ) {
            return '\\WP\\MCP\\Transport\\HttpTransport';
        }

        if ( class_exists( '\\WP\\MCP\\Transport\\Http\\RestTransport' ) ) {
            return '\\WP\\MCP\\Transport\\Http\\RestTransport';
        }

        return null;
    }

    private function adapter_installed(): bool {
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return isset( get_plugins()[self::ADAPTER_PLUGIN] );
    }

    private function adapter_active(): bool {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return is_plugin_active( self::ADAPTER_PLUGIN );
    }

    private function adapter_version(): string {
        if ( defined( 'WP\\MCP\\Core\\McpAdapter::VERSION' ) ) {
            return (string) constant( 'WP\\MCP\\Core\\McpAdapter::VERSION' );
        }

        if ( ! $this->adapter_installed() ) {
            return '';
        }

        $plugins = get_plugins();

        return isset( $plugins[self::ADAPTER_PLUGIN]['Version'] ) ? (string) $plugins[self::ADAPTER_PLUGIN]['Version'] : '';
    }

    private function enabled_tool_count(): int {
        if ( ! function_exists( 'wp_get_abilities' ) ) {
            return 0;
        }

        $count = 0;

        foreach ( wp_get_abilities() as $ability ) {
            if ( is_object( $ability ) && method_exists( $ability, 'get_name' ) && 0 === strpos( $ability->get_name(), 'formgent/' ) ) {
                $count++;
            }
        }

        return $count;
    }

    /** @param array<string,bool> $settings Current MCP settings. */
    private function state( bool $abilities, bool $installed, bool $active, bool $compatible, bool $server_active, array $settings, ?\WP_Error $server_error ): string {
        if ( ! $abilities ) {
            return 'unavailable';
        }

        if ( ! $installed ) {
            return 'action_required_install';
        }

        if ( ! $active ) {
            return 'action_required_activate';
        }

        if ( ! $compatible || null !== $server_error ) {
            return 'error';
        }

        if ( empty( $settings['enabled'] ) || empty( $settings['server'] ) ) {
            return 'ready_not_enabled';
        }

        return $server_active ? 'connected_ready' : 'error';
    }

    private function is_local_url( string $url ): bool {
        $host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );

        return in_array( $host, ['localhost', '127.0.0.1', '::1'], true )
            || '.local' === substr( $host, -6 )
            || '.test' === substr( $host, -5 );
    }
}
