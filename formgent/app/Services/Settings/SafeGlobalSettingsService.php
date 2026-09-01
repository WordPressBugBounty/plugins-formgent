<?php

namespace FormGent\App\Services\Settings;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Repositories\SettingsRepository;
use FormGent\App\Services\Mcp\McpErrorFactory;
use WP_Error;

/**
 * Reads and writes only the non-secret global settings in the MCP contract.
 */
class SafeGlobalSettingsService {
    private const DEFAULTS = [
        'disable_ip_logging'         => 'no',
        'enable_honeypot_protection' => 'yes',
        'validation_messages'        => [
            'required'        => 'This field is required',
            'email'           => 'This field must contain a valid email',
            'number'          => 'This field must contain numeric value',
            'min'             => 'This value is below the minimum {limit}',
            'max'             => 'This value exceeds the maximum {limit}',
            'confirm'         => 'Field values do not match',
            'url'             => 'Please enter a valid URL',
            'input_mask'      => 'Please fill out the field in required format',
            'gdpr'            => 'You must agree to proceed',
            'character_limit' => 'Limit is {limit} characters',
        ],
        'login_registration'         => [
            'login'        => ['status' => '0', 'page' => 0],
            'registration' => ['status' => '0', 'page' => 0],
        ],
    ];

    private SettingsRepository $repository;

    public function __construct( SettingsRepository $repository ) {
        $this->repository = $repository;
    }

    /**
     * @param array<int,string> $categories Requested public categories.
     * @return array<string,mixed>|WP_Error
     */
    public function get( array $categories = [] ) {
        $categories = empty( $categories ) ? ['general', 'validation', 'security', 'login_registration'] : array_values( array_unique( $categories ) );
        $invalid    = array_diff( $categories, ['general', 'validation', 'security', 'login_registration'] );

        if ( ! empty( $invalid ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'An unknown settings category was requested.', 'formgent' ) );
        }

        $stored   = $this->repository->get_stored();
        $settings = [];

        if ( in_array( 'general', $categories, true ) ) {
            $settings['general'] = [
                'disable_ip_logging' => $this->normalize_toggle( $stored['disable_ip_logging'] ?? self::DEFAULTS['disable_ip_logging'] ),
            ];
        }

        if ( in_array( 'validation', $categories, true ) ) {
            $saved_messages         = isset( $stored['validation_messages'] ) && is_array( $stored['validation_messages'] ) ? $stored['validation_messages'] : [];
            $messages               = array_merge( self::DEFAULTS['validation_messages'], $saved_messages );
            $settings['validation'] = [];

            foreach ( SafeSettingKeys::VALIDATION_MESSAGES as $key ) {
                $message = is_string( $messages[$key] ?? null ) ? sanitize_text_field( $messages[$key] ) : '';

                if ( '' === $message ) {
                    $message = self::DEFAULTS['validation_messages'][$key];
                }

                $settings['validation'][$key] = substr( $message, 0, 255 );
            }
        }

        if ( in_array( 'security', $categories, true ) ) {
            $settings['security'] = [
                'enable_honeypot_protection' => $this->normalize_toggle( $stored['enable_honeypot_protection'] ?? self::DEFAULTS['enable_honeypot_protection'] ),
            ];
        }

        if ( in_array( 'login_registration', $categories, true ) ) {
            $saved                          = isset( $stored['login_registration'] ) && is_array( $stored['login_registration'] ) ? $stored['login_registration'] : [];
            $settings['login_registration'] = [];

            foreach ( ['login', 'registration'] as $type ) {
                $section                               = isset( $saved[$type] ) && is_array( $saved[$type] ) ? $saved[$type] : [];
                $settings['login_registration'][$type] = [
                    'status' => '1' === (string) ( $section['status'] ?? '0' ) ? '1' : '0',
                    'page'   => absint( $section['page'] ?? 0 ),
                ];
            }
        }

        return $settings;
    }

    /**
     * @param array<string,mixed> $changes Public settings groups.
     * @return array<string,mixed>|WP_Error
     */
    public function update( array $changes ) {
        $validated = $this->validate( $changes );

        if ( is_wp_error( $validated ) ) {
            return $validated;
        }

        $stored = $this->repository->get_stored();
        $patch  = [];

        if ( isset( $validated['general'] ) ) {
            $patch['disable_ip_logging'] = $validated['general']['disable_ip_logging'];
        }

        if ( isset( $validated['security'] ) ) {
            $patch['enable_honeypot_protection'] = $validated['security']['enable_honeypot_protection'];
        }

        if ( isset( $validated['validation'] ) ) {
            $saved_messages               = isset( $stored['validation_messages'] ) && is_array( $stored['validation_messages'] ) ? $stored['validation_messages'] : [];
            $patch['validation_messages'] = array_merge( $saved_messages, $validated['validation'] );
        }

        if ( isset( $validated['login_registration'] ) ) {
            $saved_login_registration    = isset( $stored['login_registration'] ) && is_array( $stored['login_registration'] ) ? $stored['login_registration'] : [];
            $patch['login_registration'] = array_replace_recursive( $saved_login_registration, $validated['login_registration'] );
        }

        $expected = array_merge( $stored, $patch );
        $updated  = $this->repository->update_preserving( $patch );

        if ( ! $updated && $expected !== $this->repository->get_stored() ) {
            return McpErrorFactory::internal();
        }

        return $this->get( [array_key_first( $validated )] );
    }

    /**
     * @param array<string,mixed> $changes Candidate changes.
     * @return array<string,mixed>|WP_Error
     */
    private function validate( array $changes ) {
        if ( 1 !== count( $changes ) || ! empty( array_diff( array_keys( $changes ), ['general', 'validation', 'security', 'login_registration'] ) ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'Settings updates must contain exactly one known category.', 'formgent' ) );
        }

        $validated = [];

        foreach ( $changes as $category => $values ) {
            if ( ! is_array( $values ) || empty( $values ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'Each settings category must contain values.', 'formgent' ) );
            }

            if ( 'validation' === $category ) {
                $messages = $this->validate_messages( $values );

                if ( is_wp_error( $messages ) ) {
                    return $messages;
                }

                $validated[$category] = $messages;
                continue;
            }

            if ( 'login_registration' === $category ) {
                $login_registration = $this->validate_login_registration( $values );

                if ( is_wp_error( $login_registration ) ) {
                    return $login_registration;
                }

                $validated[$category] = $login_registration;
                continue;
            }

            $key = 'general' === $category ? 'disable_ip_logging' : 'enable_honeypot_protection';

            if ( [$key] !== array_keys( $values ) || ! in_array( $values[$key], ['yes', 'no'], true ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'A settings toggle must be either yes or no.', 'formgent' ) );
            }

            $validated[$category] = [$key => $values[$key]];
        }

        return $validated;
    }

    /**
     * @param array<string,mixed> $messages Candidate messages.
     * @return array<string,string>|WP_Error
     */
    private function validate_messages( array $messages ) {
        if ( ! empty( array_diff( array_keys( $messages ), SafeSettingKeys::VALIDATION_MESSAGES ) ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'An unknown validation message was provided.', 'formgent' ) );
        }

        $validated = [];

        foreach ( $messages as $key => $message ) {
            $length = is_string( $message ) ? strlen( $message ) : 0;

            if ( 1 > $length || 255 < $length ) {
                return McpErrorFactory::invalid_input( esc_html__( 'Validation messages must contain between 1 and 255 characters.', 'formgent' ) );
            }

            $validated[$key] = sanitize_text_field( $message );
        }

        return $validated;
    }

    /** @param array<string,mixed> $values @return array<string,mixed>|WP_Error */
    private function validate_login_registration( array $values ) {
        if ( ! empty( array_diff( array_keys( $values ), ['login', 'registration'] ) ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'An unknown login or registration setting was provided.', 'formgent' ) );
        }

        $validated = [];

        foreach ( $values as $type => $section ) {
            if ( ! is_array( $section ) || empty( $section ) || ! empty( array_diff( array_keys( $section ), ['status', 'page'] ) ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'A login or registration setting is invalid.', 'formgent' ) );
            }

            $status = isset( $section['status'] ) ? (string) $section['status'] : '0';
            $page   = absint( $section['page'] ?? 0 );

            if ( ! in_array( $status, ['0', '1'], true ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'Login and registration states must be 0 or 1.', 'formgent' ) );
            }

            if ( '1' === $status && ( 'page' !== get_post_type( $page ) || 'publish' !== get_post_status( $page ) ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'An enabled login or registration route requires a published page.', 'formgent' ) );
            }

            $validated[$type] = ['status' => $status, 'page' => $page];
        }

        return $validated;
    }

    /** @param mixed $value Stored toggle value. */
    private function normalize_toggle( $value ): string {
        return 'yes' === $value || true === $value || 1 === $value || '1' === $value ? 'yes' : 'no';
    }
}
