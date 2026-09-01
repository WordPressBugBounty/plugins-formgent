<?php

namespace FormGent\App\Abilities;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Repositories\McpSettingsRepository;
use FormGent\App\Services\Mcp\AbilityAccessService;
use FormGent\App\Services\Mcp\McpErrorFactory;
use FormGent\App\Services\Mcp\McpStatusService;
use WP_Error;

/**
 * Owns WordPress Abilities API and optional MCP Adapter registration.
 */
class AbilityRegistrar {
    /** @var array<int,class-string<AbstractAbility>> */
    private const CORE_ABILITIES = [
        \FormGent\App\Abilities\Forms\ListForms::class,
        \FormGent\App\Abilities\Forms\GetForm::class,
        \FormGent\App\Abilities\Forms\GetEmbedCode::class,
        \FormGent\App\Abilities\Forms\GetFormStats::class,
        \FormGent\App\Abilities\Forms\CreateForm::class,
        \FormGent\App\Abilities\Forms\UpdateForm::class,
        \FormGent\App\Abilities\Forms\GetFormAutomations::class,
        \FormGent\App\Abilities\Forms\UpdateFormAutomations::class,
        \FormGent\App\Abilities\Forms\DuplicateForm::class,
        \FormGent\App\Abilities\Forms\DeleteForm::class,
        \FormGent\App\Abilities\Settings\GetGlobalSettings::class,
        \FormGent\App\Abilities\Settings\UpdateGlobalSettings::class,
        \FormGent\App\Abilities\Analytics\GetFormAnalytics::class,
        \FormGent\App\Abilities\Responses\ListResponses::class,
        \FormGent\App\Abilities\Responses\GetResponse::class,
        \FormGent\App\Abilities\Responses\BulkGetResponses::class,
        \FormGent\App\Abilities\Responses\UpdateResponseState::class,
        \FormGent\App\Abilities\Responses\DeleteResponse::class,
    ];

    private McpSettingsRepository $settings;

    private McpStatusService $status;

    private AbilityAccessService $access;

    private static ?WP_Error $server_error = null;

    private static bool $server_registered = false;

    public function __construct( McpSettingsRepository $settings, McpStatusService $status, AbilityAccessService $access ) {
        $this->settings = $settings;
        $this->status   = $status;
        $this->access   = $access;
    }

    public function register_category(): void {
        if ( ! function_exists( 'wp_register_ability_category' ) ) {
            return;
        }

        if ( function_exists( 'wp_has_ability_category' ) && wp_has_ability_category( 'formgent' ) ) {
            return;
        }

        wp_register_ability_category(
            'formgent',
            [
                'label'       => esc_html__( 'FormGent', 'formgent' ),
                'description' => esc_html__( 'Secure form, response, analytics, and settings abilities from FormGent.', 'formgent' ),
            ]
        );
    }

    public function register_abilities(): void {
        if ( ! function_exists( 'wp_register_ability' ) || ! $this->settings->master_enabled() ) {
            return;
        }

        $abilities = [];

        foreach ( self::CORE_ABILITIES as $class_name ) {
            if ( class_exists( $class_name ) ) {
                $abilities[] = formgent_make( $class_name );
            }
        }

        /**
         * Filters FormGent's complete set of MCP abilities before registration.
         *
         * @param array<int,mixed> $abilities Ability instances.
         */
        $abilities = apply_filters( 'formgent_mcp_register_abilities', $abilities );

        foreach ( $abilities as $ability ) {
            if ( ! $this->valid( $ability ) || ! $ability->is_discoverable() ) {
                continue;
            }

            if ( function_exists( 'wp_has_ability' ) && wp_has_ability( $ability->get_id() ) ) {
                $this->skipped( $ability, 'duplicate_id' );
                continue;
            }

            if ( ! $ability->register() ) {
                $this->skipped( $ability, 'registration_failed' );
            }
        }
    }

    /**
     * @param mixed $adapter MCP Adapter instance from its lifecycle hook.
     */
    public function register_server( $adapter ): void {
        self::$server_error      = null;
        self::$server_registered = false;
        $settings                = $this->settings->get();

        if ( empty( $settings['enabled'] ) || empty( $settings['server'] ) || ! function_exists( 'wp_get_abilities' ) ) {
            return;
        }

        $transport = $this->status->transport_class();

        if ( null === $transport || ! is_object( $adapter ) || ! method_exists( $adapter, 'create_server' ) ) {
            self::$server_error = McpErrorFactory::dependency_missing( esc_html__( 'A compatible MCP Adapter transport is unavailable.', 'formgent' ) );
            return;
        }

        $tools = [];

        foreach ( wp_get_abilities() as $ability ) {
            if ( is_object( $ability ) && method_exists( $ability, 'get_name' ) && 0 === strpos( $ability->get_name(), 'formgent/' ) ) {
                $tools[] = $ability->get_name();
            }
        }

        $error_handler         = 'WP\\MCP\\Infrastructure\\ErrorHandling\\NullMcpErrorHandler';
        $observability_handler = 'WP\\MCP\\Infrastructure\\Observability\\NullMcpObservabilityHandler';

        if ( ! class_exists( $error_handler ) || ! class_exists( $observability_handler ) ) {
            self::$server_error = McpErrorFactory::dependency_missing( esc_html__( 'The MCP Adapter is missing required handler classes.', 'formgent' ) );
            return;
        }

        $result = $adapter->create_server(
            'formgent',
            'formgent/v1',
            'mcp',
            esc_html__( 'FormGent MCP Server', 'formgent' ),
            esc_html__( 'Secure FormGent form building and management.', 'formgent' ),
            (string) formgent_version(),
            [$transport],
            $error_handler,
            $observability_handler,
            $tools,
            [],
            [],
            [$this->access, 'transport_allowed']
        );

        if ( is_wp_error( $result ) ) {
            self::$server_error = McpErrorFactory::dependency_missing( esc_html__( 'The FormGent MCP server could not be registered.', 'formgent' ) );
            do_action( 'formgent_mcp_server_registration_failed', $result->get_error_code() );
            return;
        }

        self::$server_registered = true;
    }

    public static function get_server_error(): ?WP_Error {
        return self::$server_error;
    }

    public static function is_server_registered(): bool {
        return self::$server_registered;
    }

    /**
     * @param mixed $ability Candidate ability.
     */
    private function valid( $ability ): bool {
        if ( ! $ability instanceof AbstractAbility ) {
            $this->skipped( $ability, 'invalid_contract' );
            return false;
        }

        if ( 0 !== strpos( $ability->get_id(), 'formgent/' ) ) {
            $this->skipped( $ability, 'invalid_prefix' );
            return false;
        }

        $groups       = $ability->get_access_groups();
        $capabilities = $ability->get_required_capabilities();

        if ( empty( $groups ) || empty( $capabilities ) ) {
            $this->skipped( $ability, 'missing_security_policy' );
            return false;
        }

        foreach ( $groups as $group ) {
            if ( ! AccessGroup::is_valid( $group ) ) {
                $this->skipped( $ability, 'invalid_access_group' );
                return false;
            }
        }

        return true;
    }

    /** @param mixed $ability Rejected ability. */
    private function skipped( $ability, string $reason ): void {
        $ability_id = $ability instanceof AbstractAbility ? $ability->get_id() : '';
        do_action( 'formgent_mcp_ability_skipped', $ability_id, $reason );
    }
}
