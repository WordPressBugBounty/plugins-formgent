<?php

namespace FormGent\App\Http\Controllers;

defined( 'ABSPATH' ) || exit;

class RegisterController extends Controller {
    public function register() {
        check_ajax_referer( 'formgent_register_nonce', 'nonce' );

        if ( ! get_option( 'users_can_register' ) ) {
            wp_send_json_error(
                [
                    'message' => esc_html__( 'User registration is currently disabled.', 'formgent' ),
                ],
                403
            );
        }

        $form_id       = absint( $_POST['form_id'] ?? 0 );
        $first_name    = sanitize_text_field( (string) wp_unslash( $_POST['first_name'] ?? '' ) );
        $middle_name   = sanitize_text_field( (string) wp_unslash( $_POST['middle_name'] ?? '' ) );
        $last_name     = sanitize_text_field( (string) wp_unslash( $_POST['last_name'] ?? '' ) );
        $username      = sanitize_user( (string) wp_unslash( $_POST['username'] ?? '' ), true );
        $email         = sanitize_email( (string) wp_unslash( $_POST['email'] ?? '' ) );
        $email_confirm = sanitize_email( (string) wp_unslash( $_POST['email_confirm'] ?? '' ) );
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Passwords must not be sanitized; only validated here and created during final response submission.
        $password = (string) wp_unslash( $_POST['password'] ?? '' );
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Password confirmation must not be sanitized; only compared against the password field.
        $password_confirm = (string) wp_unslash( $_POST['password_confirmation'] ?? '' );

        // Validate required fields.
        $errors                = [];
        $register_child_fields = $this->get_register_child_fields( $form_id );

        $this->validate_register_text_field(
            $errors,
            'first_name',
            $first_name,
            $register_child_fields['first_name'] ?? []
        );
        $this->validate_register_text_field(
            $errors,
            'middle_name',
            $middle_name,
            $register_child_fields['middle_name'] ?? []
        );
        $this->validate_register_text_field(
            $errors,
            'last_name',
            $last_name,
            $register_child_fields['last_name'] ?? []
        );
        $this->validate_register_text_field(
            $errors,
            'username',
            $username,
            $register_child_fields['username'] ?? []
        );

        if ( empty( $email ) ) {
            $errors['email'] = esc_html__( 'Email is required.', 'formgent' );
        } elseif ( ! is_email( $email ) ) {
            $errors['email'] = esc_html__( 'Please enter a valid email address.', 'formgent' );
        } elseif ( email_exists( $email ) ) {
            $errors['email'] = esc_html__( 'This email is already registered.', 'formgent' );
        }

        $email_field = $this->get_register_email_field( $form_id );
        if ( empty( $errors['email'] ) &&
            ! empty( $email_field['enable_confirmation_field'] ) &&
            $email !== $email_confirm
        ) {
            $errors['email_confirm'] = esc_html__( 'Emails do not match.', 'formgent' );
        }

        if ( empty( $password ) ) {
            $errors['password'] = esc_html__( 'Password is required.', 'formgent' );
        } elseif ( $password !== $password_confirm ) {
            $errors['password'] = esc_html__( 'Passwords do not match.', 'formgent' );
        }

        $password_field = $this->get_register_password_field( $form_id );
        if ( empty( $errors['password'] ) &&
            ! empty( $password_field['enable_password_strength'] ) &&
            ! $this->is_password_strong_enough(
                $password,
                isset( $password_field['minimum_strength'] ) ? (string) $password_field['minimum_strength'] : 'medium'
            )
        ) {
            $errors['password'] = esc_html__( 'Password strength is too weak.', 'formgent' );
        }

        if ( empty( $errors['username'] ) && ! empty( $username ) && username_exists( $username ) ) {
            $errors['username'] = esc_html__( 'This username is already taken.', 'formgent' );
        }

        if ( ! empty( $errors ) ) {
            wp_send_json_error(
                [
                    'message' => esc_html__( 'Registration failed. Please fix the errors.', 'formgent' ),
                    'errors'  => $errors,
                ],
                422
            );
        }

        $auto_login = false;
        if ( function_exists( 'formgent_form_get_setting' ) && $form_id ) {
            $registrations = formgent_form_get_setting( $form_id, 'user_registrations', [] );
            if ( is_array( $registrations ) && ! empty( $registrations ) ) {
                $first_registration = reset( $registrations );
                if ( is_array( $first_registration ) ) {
                    $auto_login = ! empty( $first_registration['auto_login'] );
                }
            }
        }

        wp_send_json_success(
            [
                'message'            => esc_html__( 'Registration details validated.', 'formgent' ),
                'pending_submission' => true,
                'auto_login'         => (bool) $auto_login,
            ]
        );
    }

    private function get_register_password_field( int $form_id ): array {
        return $this->get_register_child_field_by_name( $form_id, 'password' );
    }

    private function get_register_email_field( int $form_id ): array {
        return $this->get_register_child_field_by_name( $form_id, 'email' );
    }

    private function get_register_child_field_by_name( int $form_id, string $field_name ): array {
        $children = $this->get_register_child_fields( $form_id );

        if ( isset( $children[ $field_name ] ) && is_array( $children[ $field_name ] ) ) {
            return $children[ $field_name ];
        }

        return [];
    }

    private function get_register_child_fields( int $form_id ): array {
        $fields = $this->get_form_fields( $form_id );

        foreach ( $fields as $field ) {
            if ( ! is_array( $field ) || ( $field['field_type'] ?? '' ) !== 'register' ) {
                continue;
            }

            $children = $field['children'] ?? [];
            if ( ! is_array( $children ) ) {
                continue;
            }

            return $children;
        }

        return [];
    }

    private function get_form_fields( int $form_id ): array {
        if ( ! $form_id || ! function_exists( 'formgent_get_form_by_id' ) || ! function_exists( 'formgent_get_form_fields' ) ) {
            return [];
        }

        $form = formgent_get_form_by_id( $form_id );
        if ( ! $form ) {
            return [];
        }

        $fields = formgent_get_form_fields( $form );
        return is_array( $fields ) ? $fields : [];
    }

    private function validate_register_text_field( array &$errors, string $field_name, string $value, array $field ): void {
        if ( empty( $field ) ) {
            return;
        }

        $value = trim( $value );
        $label = $this->get_register_field_label( $field, $field_name );

        if ( ! empty( $field['required'] ) && '' === $value ) {
            $errors[ $field_name ] = sprintf(
                /* translators: %s: field label. */
                esc_html__( '%s is required.', 'formgent' ),
                $label
            );
            return;
        }

        if ( empty( $field['character_limit'] ) ) {
            return;
        }

        $limit = absint( $field['limit'] ?? 0 );
        if ( $this->get_string_length( $value ) > $limit ) {
            $errors[ $field_name ] = sprintf(
                /* translators: 1: field label, 2: character limit. */
                esc_html__( '%1$s must not exceed %2$d characters.', 'formgent' ),
                $label,
                $limit
            );
        }
    }

    private function get_register_field_label( array $field, string $field_name ): string {
        $label = isset( $field['label'] ) ? trim( wp_strip_all_tags( (string) $field['label'] ) ) : '';

        if ( '' !== $label ) {
            return $label;
        }

        return ucwords( str_replace( '_', ' ', $field_name ) );
    }

    private function get_string_length( string $value ): int {
        if ( function_exists( 'mb_strlen' ) ) {
            return mb_strlen( $value );
        }

        return strlen( $value );
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

        if ( $length >= 10 && $has_lower && $has_upper && $has_number && $has_symbol ) {
            return 'strong';
        }

        if ( $length >= 8 && ( $has_lower || $has_upper ) && $has_number ) {
            return 'medium';
        }

        if ( $length >= 6 ) {
            return 'weak';
        }

        return 'invalid';
    }
}
