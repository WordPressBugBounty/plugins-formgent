<?php

namespace FormGent\App\Services\Forms;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Utils\Capabilities;

/**
 * Shared write-time policy for settings that can execute code or assign roles.
 */
final class FormSecurityPolicy {
    /**
     * @param array<string,mixed> $settings
     * @return array<string,mixed>
     */
    public static function sanitize_settings( array $settings, bool $custom_script_changed = false ): array {
        if ( isset( $settings['confirmation'] ) && is_array( $settings['confirmation'] ) ) {
            $settings['confirmation']['message'] = wp_kses_post( (string) ( $settings['confirmation']['message'] ?? '' ) );
        }

        if ( $custom_script_changed ) {
            if ( current_user_can( 'unfiltered_html' ) && is_array( $settings['customScript'] ?? null ) ) {
                $settings['customScript'] = [
                    'css'         => substr( (string) ( $settings['customScript']['css'] ?? '' ), 0, 100000 ),
                    'js'          => substr( (string) ( $settings['customScript']['js'] ?? '' ), 0, 100000 ),
                    '_authorized' => true,
                ];
            } else {
                unset( $settings['customScript'] );
            }
        }

        if ( isset( $settings['user_registrations'] ) && is_array( $settings['user_registrations'] ) ) {
            $settings['user_registrations'] = array_map( [self::class, 'sanitize_registration'], $settings['user_registrations'] );
        }

        return $settings;
    }

    /** @param mixed $registration @return mixed */
    private static function sanitize_registration( $registration ) {
        if ( ! is_array( $registration ) ) {
            return $registration;
        }

        $role        = sanitize_key( (string) ( $registration['user_role'] ?? 'subscriber' ) );
        $custom_role = sanitize_key( (string) ( $registration['custom_role'] ?? '' ) );
        $selected    = 'custom' === $role ? $custom_role : $role;

        if ( ! Capabilities::is_safe_registration_role( $selected ) ) {
            $registration['user_role']   = 'subscriber';
            $registration['custom_role'] = '';
        }

        return $registration;
    }
}
