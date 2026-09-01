<?php

namespace FormGent\App\Utils;

defined( 'ABSPATH' ) || exit;

final class Capabilities {
    public const ACCESS                          = 'formgent_access';
    public const MANAGE_FORMS                    = 'formgent_manage_forms';
    public const READ_FORMS                      = 'formgent_read_forms';
    public const CREATE_FORMS                    = 'formgent_create_forms';
    public const EDIT_FORMS                      = 'formgent_edit_forms';
    public const DELETE_FORMS                    = 'formgent_delete_forms';
    public const PUBLISH_FORMS                   = 'formgent_publish_forms';
    public const READ_RESPONSES                  = 'formgent_read_responses';
    public const EDIT_RESPONSES                  = 'formgent_edit_responses';
    public const DELETE_RESPONSES                = 'formgent_delete_responses';
    public const READ_SETTINGS                   = 'formgent_read_settings';
    public const EDIT_SETTINGS                   = 'formgent_edit_settings';
    public const READ_AUTOMATIONS                = 'formgent_read_automations';
    public const EDIT_AUTOMATIONS                = 'formgent_edit_automations';
    public const MANAGE_REGISTRATION_AUTOMATIONS = 'formgent_manage_registration_automations';
    public const MANAGE_CPT_AUTOMATIONS          = 'formgent_manage_cpt_automations';

    public const MCP_OPERATOR_ROLE = 'formgent_mcp_operator';

    private const VERSION    = '2';
    private const OPTION_KEY = 'formgent_capabilities_version';

    private const FORBIDDEN_USER_CAPABILITIES = [
        'create_users',
        'edit_users',
        'delete_users',
        'promote_users',
        'list_users',
        'remove_users',
        'add_users',
    ];

    public static function all(): array {
        return [
            self::ACCESS,
            self::MANAGE_FORMS,
            ...self::form_action_capabilities(),
            ...self::mcp_action_capabilities(),
        ];
    }

    public static function form_action_capabilities(): array {
        return [
            self::READ_FORMS,
            self::CREATE_FORMS,
            self::EDIT_FORMS,
            self::DELETE_FORMS,
            self::PUBLISH_FORMS,
        ];
    }

    public static function mcp_action_capabilities(): array {
        return [
            self::READ_RESPONSES,
            self::EDIT_RESPONSES,
            self::DELETE_RESPONSES,
            self::READ_SETTINGS,
            self::EDIT_SETTINGS,
            self::READ_AUTOMATIONS,
            self::EDIT_AUTOMATIONS,
            self::MANAGE_REGISTRATION_AUTOMATIONS,
            self::MANAGE_CPT_AUTOMATIONS,
        ];
    }

    /**
     * Determines whether MCP registration automation may assign a role.
     *
     * MCP-created registrations must never grant WordPress user-management or
     * FormGent-management access, including the dedicated MCP operator role.
     */
    public static function is_safe_registration_role( string $role_name ): bool {
        if ( in_array( $role_name, ['administrator', 'editor', self::MCP_OPERATOR_ROLE], true ) ) {
            return false;
        }

        $role = get_role( $role_name );

        if ( ! $role ) {
            return false;
        }

        $restricted = [
            'manage_options',
            ...self::FORBIDDEN_USER_CAPABILITIES,
            ...self::all(),
        ];

        foreach ( array_unique( $restricted ) as $capability ) {
            if ( $role->has_cap( $capability ) ) {
                return false;
            }
        }

        return true;
    }

    public static function filter_user_has_cap( array $allcaps ): array {
        if ( ! empty( $allcaps['manage_options'] ) || ! empty( $allcaps[self::MANAGE_FORMS] ) ) {
            $allcaps[self::ACCESS] = true;

            foreach ( self::form_action_capabilities() as $capability ) {
                $allcaps[$capability] = true;
            }

            return $allcaps;
        }

        foreach ( self::form_action_capabilities() as $capability ) {
            if ( ! empty( $allcaps[$capability] ) ) {
                $allcaps[self::ACCESS] = true;
                break;
            }
        }

        return $allcaps;
    }

    public static function maybe_install(): void {
        if ( self::VERSION === get_option( self::OPTION_KEY ) ) {
            return;
        }

        self::install();
    }

    public static function install(): void {
        $wp_roles = wp_roles();

        foreach ( array_keys( $wp_roles->roles ) as $role_name ) {
            $role = get_role( $role_name );

            if ( ! $role ) {
                continue;
            }

            if ( 'administrator' === $role_name || ! empty( $role->capabilities['manage_options'] ) ) {
                foreach ( self::all() as $capability ) {
                    $role->add_cap( $capability );
                }
            }
        }

        self::install_mcp_operator_role();

        update_option( self::OPTION_KEY, self::VERSION );
    }

    private static function install_mcp_operator_role(): void {
        $role = get_role( self::MCP_OPERATOR_ROLE );

        if ( ! $role ) {
            add_role(
                self::MCP_OPERATOR_ROLE,
                esc_html__( 'FormGent MCP Operator', 'formgent' ),
                ['read' => true]
            );

            $role = get_role( self::MCP_OPERATOR_ROLE );
        }

        if ( ! $role ) {
            return;
        }

        foreach ( self::all() as $capability ) {
            $role->add_cap( $capability );
        }

        foreach ( self::FORBIDDEN_USER_CAPABILITIES as $capability ) {
            $role->remove_cap( $capability );
        }

        $role->remove_cap( 'manage_options' );
    }

    public static function can_access(): bool {
        return self::current_user_can_any( self::all() );
    }

    public static function can_manage_forms(): bool {
        return self::current_user_can_any( [ self::MANAGE_FORMS ] );
    }

    public static function can_read_forms(): bool {
        return self::current_user_can_form_action( self::READ_FORMS );
    }

    public static function can_create_forms(): bool {
        return self::current_user_can_form_action( self::CREATE_FORMS );
    }

    public static function can_edit_forms(): bool {
        return self::current_user_can_form_action( self::EDIT_FORMS );
    }

    public static function can_delete_forms(): bool {
        return self::current_user_can_form_action( self::DELETE_FORMS );
    }

    public static function can_publish_forms(): bool {
        return self::current_user_can_form_action( self::PUBLISH_FORMS );
    }

    public static function current_user_permissions(): array {
        return [
            'access'        => self::can_access(),
            'manage_forms'  => self::can_manage_forms(),
            'read_forms'    => self::can_read_forms(),
            'create_forms'  => self::can_create_forms(),
            'edit_forms'    => self::can_edit_forms(),
            'delete_forms'  => self::can_delete_forms(),
            'publish_forms' => self::can_publish_forms(),
            'manage_site'   => current_user_can( 'manage_options' ),
        ];
    }

    private static function current_user_can_form_action( string $capability ): bool {
        return self::current_user_can_any( [ self::MANAGE_FORMS, $capability ] );
    }

    private static function current_user_can_any( array $capabilities ): bool {
        if ( current_user_can( 'manage_options' ) ) {
            return true;
        }

        foreach ( $capabilities as $capability ) {
            if ( current_user_can( $capability ) ) {
                return true;
            }
        }

        return false;
    }
}
