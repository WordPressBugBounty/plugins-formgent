<?php

namespace FormGent\App\Utils;

defined( 'ABSPATH' ) || exit;

final class Capabilities {
    public const ACCESS        = 'formgent_access';
    public const MANAGE_FORMS  = 'formgent_manage_forms';
    public const READ_FORMS    = 'formgent_read_forms';
    public const CREATE_FORMS  = 'formgent_create_forms';
    public const EDIT_FORMS    = 'formgent_edit_forms';
    public const DELETE_FORMS  = 'formgent_delete_forms';
    public const PUBLISH_FORMS = 'formgent_publish_forms';

    private const VERSION    = '1';
    private const OPTION_KEY = 'formgent_capabilities_version';

    public static function all(): array {
        return [
            self::ACCESS,
            self::MANAGE_FORMS,
            ...self::form_action_capabilities(),
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

        update_option( self::OPTION_KEY, self::VERSION );
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
