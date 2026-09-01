<?php

namespace FormGent\App\Http\Controllers\Admin;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Http\Controllers\Controller;
use FormGent\App\Repositories\McpSettingsRepository;
use FormGent\App\Services\Mcp\McpStatusService;
use FormGent\WpMVC\Routing\Response;
use InvalidArgumentException;
use RuntimeException;
use WP_REST_Request;

/**
 * Administrator-only setup API for FormGent's MCP integration.
 */
class McpController extends Controller {
    private McpSettingsRepository $settings;

    private McpStatusService $status;

    public function __construct( McpSettingsRepository $settings, McpStatusService $status ) {
        $this->settings = $settings;
        $this->status   = $status;
    }

    public function index() {
        return Response::send( $this->status->get() );
    }

    public function update_settings( WP_REST_Request $request ) {
        $settings = $request->get_param( 'settings' );

        if ( ! is_array( $settings ) ) {
            return Response::send( ['message' => esc_html__( 'A settings object is required.', 'formgent' )], 400 );
        }

        try {
            $settings = $this->settings->update( $settings );
        } catch ( InvalidArgumentException $exception ) {
            return Response::send( ['message' => esc_html__( 'Only known boolean MCP settings may be changed.', 'formgent' )], 400 );
        } catch ( RuntimeException $exception ) {
            return Response::send( ['message' => esc_html__( 'The AI connection settings could not be saved.', 'formgent' )], 500 );
        }

        return Response::send(
            [
                'settings' => $settings,
                'message'  => esc_html__( 'AI connection settings have been saved.', 'formgent' ),
            ]
        );
    }

    public function activate_adapter() {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return Response::send( ['message' => esc_html__( 'You are not allowed to activate plugins.', 'formgent' )], 403 );
        }

        if ( ! function_exists( 'get_plugins' ) || ! function_exists( 'activate_plugin' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = get_plugins();

        if ( ! isset( $plugins[McpStatusService::ADAPTER_PLUGIN] ) ) {
            return Response::send( ['message' => esc_html__( 'The official MCP Adapter is not installed.', 'formgent' )], 404 );
        }

        if ( is_plugin_active( McpStatusService::ADAPTER_PLUGIN ) ) {
            return Response::send( ['message' => esc_html__( 'The MCP Adapter is already active.', 'formgent' )] );
        }

        $result = activate_plugin( McpStatusService::ADAPTER_PLUGIN, '', is_multisite() && is_network_admin(), false );

        if ( is_wp_error( $result ) ) {
            return Response::send( ['message' => esc_html__( 'The MCP Adapter could not be activated.', 'formgent' )], 500 );
        }

        return Response::send( ['message' => esc_html__( 'The MCP Adapter has been activated.', 'formgent' )] );
    }
}
