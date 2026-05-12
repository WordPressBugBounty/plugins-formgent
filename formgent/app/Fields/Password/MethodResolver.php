<?php

namespace FormGent\App\Fields\Password;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Summary\Pagination;
use FormGent\WpMVC\Exceptions\Exception;
use FormGent\WpMVC\RequestValidator\Validator;
use stdClass;
use WP_REST_Request;

trait MethodResolver {

    use Pagination;

    public static function get_key(): string {
        return 'password';
    }

    protected function get_validation_rules( array $field ): array {
        return ['string'];
    }

    public function validate( array $field, WP_REST_Request $wp_rest_request, Validator $validator, stdClass $form ) {
        parent::validate( $field, $wp_rest_request, $validator, $form );

        $raw_password = (string) $wp_rest_request->get_param( $field['name'] );

        if ( '' === $raw_password ) {
            return;
        }

        $is_admin_edit = (bool) $wp_rest_request->get_param( '_formgent_admin_edit' );

        // Password confirmation is a user-facing anti-typo UX feature.
        // When admins edit an entry, we skip confirmation validation.
        if ( ! $is_admin_edit && ! empty( $field['enable_confirmation_field'] ) ) {
            $validator->validate(
                [
                    $field['name'] => 'confirmed',
                ]
            );
        }

        if ( empty( $field['enable_password_strength'] ) ) {
            return;
        }

        $minimum_strength = isset( $field['minimum_strength'] ) ? (string) $field['minimum_strength'] : 'medium';

        if ( $this->is_password_strong_enough( $raw_password, $minimum_strength ) ) {
            return;
        }

        $validator_messages = [
            $field['name'] => [
                esc_html__( 'Password strength is too weak.', 'formgent' ),
            ],
        ];

        throw new Exception( esc_html__( 'Validation failed.', 'formgent' ), 422, null, $validator_messages );
    }

    private function is_password_strong_enough( string $password, string $minimum_strength ): bool {
        $strength_map = [
            'invalid' => -1,
            'weak'    => 0,
            'medium'  => 1,
            'strong'  => 2,
        ];

        $minimum = $strength_map[ $minimum_strength ] ?? $strength_map['medium'];
        $level   = $this->get_password_strength_level( $password );
        $actual  = $strength_map[ $level ] ?? $strength_map['invalid'];

        return $actual >= $minimum;
    }

    private function get_password_strength_level( string $password ): string {
        $length = strlen( $password );

        $has_lower  = (bool) preg_match( '/[a-z]/', $password );
        $has_upper  = (bool) preg_match( '/[A-Z]/', $password );
        $has_number = (bool) preg_match( '/[0-9]/', $password );
        $has_symbol = (bool) preg_match( '/[^a-zA-Z0-9]/', $password );

        // Strong: long + mixed characters.
        if ( $length >= 10 && $has_lower && $has_upper && $has_number && $has_symbol ) {
            return 'strong';
        }

        // Medium: decent length + alpha + number.
        if ( $length >= 8 && ( $has_lower || $has_upper ) && $has_number ) {
            return 'medium';
        }

        // Weak: minimal length.
        if ( $length >= 6 ) {
            return 'weak';
        }

        return 'invalid';
    }
}

