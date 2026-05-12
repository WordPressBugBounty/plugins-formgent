<?php

namespace FormGent\App\Providers;

defined( "ABSPATH" ) || exit;

use FormGent\App\DTO\AnswerFieldDTO;
use FormGent\App\Services\UserVerificationService;
use FormGent\WpMVC\Contracts\Provider;
use stdClass;
use WP_REST_Request;

class UserRegistrationServiceProvider implements Provider {
    private array $response_registration_results = [];

    public function boot() {
        add_filter( 'formgent_before_store_form_response', [ $this, 'validate_before_store_form_response' ], 20, 4 );
        add_action( 'formgent_after_create_form_response', [ $this, 'after_create_form_response' ], 10, 3 );
        add_filter( 'formgent_form_submission_response', [ $this, 'add_registration_data_to_submission_response' ], 20, 4 );
    }

    public function after_create_form_response( int $response_id, stdClass $form, WP_REST_Request $request ) {
        if ( $request->get_param( 'payment_gateway' ) ) {
            return;
        }

        $this->create_users_for_response( $response_id, $form, $request );
    }

    public function validate_before_store_form_response( $result, stdClass $response, stdClass $form, WP_REST_Request $request ) {
        if ( is_wp_error( $result ) || false === $result ) {
            return $result;
        }

        $validation = $this->validate_register_block_submission( $form, $request );
        if ( empty( $validation['errors'] ) ) {
            return $result;
        }

        return new \WP_Error(
            422,
            esc_html__( 'Registration failed. Please fix the errors.', 'formgent' ),
            [ 'errors' => $validation['errors'] ]
        );
    }

    public function add_registration_data_to_submission_response( array $response_data, WP_REST_Request $request, stdClass $form, stdClass $response ): array {
        if ( $request->get_param( 'payment_gateway' ) && empty( $response_data['payment_data']['success'] ) ) {
            return $response_data;
        }

        $results = $this->create_users_for_response( (int) $response->id, $form, $request );
        $primary = $this->get_primary_registration_result( $results );

        if ( ! empty( $primary ) ) {
            $response_data['registration_data'] = $primary;
        }

        return $response_data;
    }

    private function create_users_for_response( int $response_id, stdClass $form, WP_REST_Request $request ): array {
        if ( isset( $this->response_registration_results[$response_id] ) ) {
            return $this->response_registration_results[$response_id];
        }

        $results = [];

        if ( ! get_option( 'users_can_register' ) ) {
            $this->response_registration_results[$response_id] = $results;
            return $results;
        }

        $registrations = formgent_form_get_setting( (int) $form->ID, 'user_registrations', [] );
        $answers       = formgent_get_form_answers( (int) $form->ID, $response_id, true );

        $register_block_result = $this->create_register_block_user( $response_id, $form, $request, is_array( $registrations ) ? $registrations : [], is_array( $answers ) ? $answers : [] );
        if ( ! empty( $register_block_result ) ) {
            $results[] = $register_block_result;
        }

        if ( is_array( $registrations ) && ! empty( $registrations ) && ! empty( $answers ) ) {
            $results = array_merge( $results, $this->create_mapped_registration_users( $response_id, $form, $request, $registrations, $answers ) );
        }

        $this->response_registration_results[$response_id] = $results;
        return $results;
    }

    private function create_mapped_registration_users( int $response_id, stdClass $form, WP_REST_Request $request, array $registrations, array $answers ): array {
        $results = [];

        foreach ( $registrations as $registration ) {
            if ( ! is_array( $registration ) ) {
                continue;
            }

            $field_mapping = $registration['field_mapping'] ?? [];
            if ( is_string( $field_mapping ) ) {
                $decoded       = json_decode( $field_mapping, true );
                $field_mapping = is_array( $decoded ) ? $decoded : [];
            }
            if ( ! is_array( $field_mapping ) || empty( $field_mapping ) ) {
                continue;
            }

            $email = sanitize_email( $this->get_mapped_field_value( $form, $request, $answers, $field_mapping, 'email' ) );
            if ( empty( $email ) || ! is_email( $email ) || email_exists( $email ) ) {
                continue;
            }

            $username_value = $this->get_mapped_field_value( $form, $request, $answers, $field_mapping, 'username' );
            if ( '' === $username_value ) {
                $username_value = current( explode( '@', $email ) );
            }

            $username = $this->make_unique_username( sanitize_user( $username_value, true ) );
            if ( '' === $username ) {
                continue;
            }

            $password = $this->get_mapped_field_value( $form, $request, $answers, $field_mapping, 'password' );
            if ( '' === $password ) {
                $password = wp_generate_password( 16, true, true );
            }

            $first_name  = $this->get_mapped_field_value( $form, $request, $answers, $field_mapping, 'first_name' );
            $last_name   = $this->get_mapped_field_value( $form, $request, $answers, $field_mapping, 'last_name' );
            $middle_name = $this->get_mapped_field_value( $form, $request, $answers, $field_mapping, 'middle_name' );

            $role = $this->resolve_user_role( $registration );

            $user_data = [
                'user_login' => $username,
                'user_pass'  => $password,
                'user_email' => $email,
                'role'       => $role,
            ];

            if ( '' !== $first_name ) {
                $user_data['first_name'] = $first_name;
            }
            if ( '' !== $last_name ) {
                $user_data['last_name'] = $last_name;
            }
            if ( '' !== $first_name || '' !== $last_name ) {
                $user_data['display_name'] = trim( $first_name . ' ' . $last_name );
            }

            $created_user_id = wp_insert_user( $user_data );
            if ( is_wp_error( $created_user_id ) ) {
                $results[] = [
                    'source'  => 'registration_feed',
                    'success' => false,
                    'message' => implode( ' ', $created_user_id->get_error_messages() ),
                ];
                continue;
            }

            if ( '' !== $middle_name ) {
                update_user_meta( $created_user_id, 'middle_name', $middle_name );
            }

            $custom_meta = $registration['custom_meta'] ?? [];
            if ( is_array( $custom_meta ) ) {
                foreach ( $custom_meta as $meta_row ) {
                    if ( ! is_array( $meta_row ) ) {
                        continue;
                    }
                    $meta_key  = isset( $meta_row['meta_key'] ) ? sanitize_key( (string) $meta_row['meta_key'] ) : '';
                    $field_key = isset( $meta_row['field'] ) ? (string) $meta_row['field'] : '';
                    if ( '' === $meta_key || '' === $field_key ) {
                        continue;
                    }
                    $meta_value = $this->get_field_value_by_mapping_key( $form, $request, $answers, $field_key );
                    if ( '' === $meta_value ) {
                        continue;
                    }
                    update_user_meta( $created_user_id, $meta_key, sanitize_text_field( $meta_value ) );
                }
            }

            formgent_singleton( UserVerificationService::class )->setup_user_verification(
                (int) $created_user_id,
                $registration,
                (int) $form->ID
            );

            if ( ! empty( $registration['auto_login'] ) ) {
                wp_set_current_user( (int) $created_user_id );
                wp_set_auth_cookie( (int) $created_user_id, true );
            }

            /**
             * Fires after FormGent creates a WordPress user from a registration feed.
             *
             * @param int      $created_user_id
             * @param int      $response_id
             * @param stdClass $form
             * @param array    $registration
             */
            do_action( 'formgent_after_create_registered_user', (int) $created_user_id, $response_id, $form, $registration );

            $results[] = [
                'source'            => 'registration_feed',
                'success'           => true,
                'message'           => esc_html__( 'Registration successful.', 'formgent' ),
                'user_id'           => (int) $created_user_id,
                'auto_logged_in'    => ! empty( $registration['auto_login'] ),
                'logged_in_message' => $this->get_logged_in_message( $registration, (int) $created_user_id ),
                'redirect_url'      => ! empty( $registration['auto_login'] )
                    ? apply_filters( 'formgent_register_redirect_url', home_url( '/' ), $created_user_id )
                    : '',
            ];
        }

        return $results;
    }

    private function create_register_block_user( int $response_id, stdClass $form, WP_REST_Request $request, array $registrations, array $answers ): array {
        $submission = $this->get_register_block_submission( $form, $request );
        if ( empty( $submission ) ) {
            return [];
        }

        $validation = $this->validate_register_block_values( $submission['field'], $submission['values'] );
        if ( ! empty( $validation['errors'] ) ) {
            return [
                'source'  => 'register_block',
                'success' => false,
                'message' => esc_html__( 'Registration failed. Please fix the errors.', 'formgent' ),
                'errors'  => $validation['errors'],
            ];
        }

        $values              = $validation['values'];
        $active_registration = $this->get_active_registration( $registrations );
        $auto_login          = ! empty( $active_registration['auto_login'] );
        $role                = $this->resolve_user_role( $active_registration );
        $username            = $values['username'];

        if ( '' === $username ) {
            $username = sanitize_user( current( explode( '@', $values['email'] ) ), true );
        }

        $username = $this->make_unique_username( $username );
        if ( '' === $username ) {
            return [
                'source'  => 'register_block',
                'success' => false,
                'message' => esc_html__( 'Registration failed. Please try again.', 'formgent' ),
            ];
        }

        $user_data = [
            'user_login' => $username,
            'user_pass'  => $values['password'],
            'user_email' => $values['email'],
            'role'       => $role,
        ];

        if ( '' !== $values['first_name'] ) {
            $user_data['first_name'] = $values['first_name'];
        }
        if ( '' !== $values['last_name'] ) {
            $user_data['last_name'] = $values['last_name'];
        }
        if ( '' !== $values['first_name'] || '' !== $values['last_name'] ) {
            $user_data['display_name'] = trim( $values['first_name'] . ' ' . $values['last_name'] );
        }

        $created_user_id = wp_insert_user( $user_data );
        if ( is_wp_error( $created_user_id ) ) {
            return [
                'source'  => 'register_block',
                'success' => false,
                'message' => implode( ' ', $created_user_id->get_error_messages() ),
            ];
        }

        if ( '' !== $values['middle_name'] ) {
            update_user_meta( $created_user_id, 'middle_name', $values['middle_name'] );
        }

        $this->store_custom_user_meta( (int) $created_user_id, $form, $request, $answers, $active_registration );

        $verification_result = [
            'message' => '',
        ];
        if ( ! empty( $active_registration ) ) {
            $verification_result = formgent_singleton( UserVerificationService::class )->setup_user_verification(
                (int) $created_user_id,
                $active_registration,
                (int) $form->ID
            );
        }

        if ( $auto_login ) {
            wp_set_current_user( (int) $created_user_id );
            wp_set_auth_cookie( (int) $created_user_id, true );
        }

        /**
         * Fires after FormGent creates a WordPress user from the frontend register block.
         *
         * @param int   $created_user_id
         * @param int   $form_id
         * @param array $registrations
         */
        do_action( 'formgent_after_frontend_register', (int) $created_user_id, (int) $form->ID, $registrations );

        return [
            'source'            => 'register_block',
            'success'           => true,
            'message'           => $verification_result['message'] ?: esc_html__( 'Registration successful.', 'formgent' ),
            'user_id'           => (int) $created_user_id,
            'auto_logged_in'    => (bool) $auto_login,
            'logged_in_message' => $this->get_logged_in_message( $active_registration, (int) $created_user_id ),
            'redirect_url'      => $auto_login
                ? apply_filters( 'formgent_register_redirect_url', home_url( '/' ), $created_user_id )
                : '',
        ];
    }

    private function validate_register_block_submission( stdClass $form, WP_REST_Request $request ): array {
        $submission = $this->get_register_block_submission( $form, $request );
        if ( empty( $submission ) ) {
            return [
                'errors' => [],
                'values' => [],
            ];
        }

        if ( ! get_option( 'users_can_register' ) ) {
            return [
                'errors' => [
                    'registration' => esc_html__( 'User registration is currently disabled.', 'formgent' ),
                ],
                'values' => [],
            ];
        }

        return $this->validate_register_block_values( $submission['field'], $submission['values'] );
    }

    private function validate_register_block_values( array $register_field, array $raw_values ): array {
        $children = isset( $register_field['children'] ) && is_array( $register_field['children'] )
            ? $register_field['children']
            : [];

        $values = [
            'first_name'            => sanitize_text_field( (string) ( $raw_values['first_name'] ?? '' ) ),
            'middle_name'           => sanitize_text_field( (string) ( $raw_values['middle_name'] ?? '' ) ),
            'last_name'             => sanitize_text_field( (string) ( $raw_values['last_name'] ?? '' ) ),
            'username'              => sanitize_user( (string) ( $raw_values['username'] ?? '' ), true ),
            'email'                 => sanitize_email( (string) ( $raw_values['email'] ?? '' ) ),
            'email_confirmation'    => sanitize_email( (string) ( $raw_values['email_confirmation'] ?? $raw_values['email_confirm'] ?? '' ) ),
            'password'              => (string) ( $raw_values['password'] ?? '' ),
            'password_confirmation' => (string) ( $raw_values['password_confirmation'] ?? '' ),
        ];
        $errors = [];

        $this->validate_register_text_field( $errors, 'first_name', $values['first_name'], $children['first_name'] ?? [] );
        $this->validate_register_text_field( $errors, 'middle_name', $values['middle_name'], $children['middle_name'] ?? [] );
        $this->validate_register_text_field( $errors, 'last_name', $values['last_name'], $children['last_name'] ?? [] );
        $this->validate_register_text_field( $errors, 'username', $values['username'], $children['username'] ?? [] );

        if ( empty( $values['email'] ) ) {
            $errors['email'] = esc_html__( 'Email is required.', 'formgent' );
        } elseif ( ! is_email( $values['email'] ) ) {
            $errors['email'] = esc_html__( 'Please enter a valid email address.', 'formgent' );
        } elseif ( email_exists( $values['email'] ) ) {
            $errors['email'] = esc_html__( 'This email is already registered.', 'formgent' );
        }

        $email_field = $children['email'] ?? [];
        if ( empty( $errors['email'] ) &&
            ! empty( $email_field['enable_confirmation_field'] ) &&
            $values['email'] !== $values['email_confirmation']
        ) {
            $errors['email_confirm'] = esc_html__( 'Emails do not match.', 'formgent' );
        }

        if ( empty( $values['password'] ) ) {
            $errors['password'] = esc_html__( 'Password is required.', 'formgent' );
        } elseif ( $values['password'] !== $values['password_confirmation'] ) {
            $errors['password'] = esc_html__( 'Passwords do not match.', 'formgent' );
        }

        $password_field = $children['password'] ?? [];
        if ( empty( $errors['password'] ) &&
            ! empty( $password_field['enable_password_strength'] ) &&
            ! $this->is_password_strong_enough(
                $values['password'],
                isset( $password_field['minimum_strength'] ) ? (string) $password_field['minimum_strength'] : 'medium'
            )
        ) {
            $errors['password'] = esc_html__( 'Password strength is too weak.', 'formgent' );
        }

        if ( empty( $errors['username'] ) && '' !== $values['username'] && username_exists( $values['username'] ) ) {
            $errors['username'] = esc_html__( 'This username is already taken.', 'formgent' );
        }

        return compact( 'errors', 'values' );
    }

    private function get_register_block_submission( stdClass $form, WP_REST_Request $request ): array {
        $form_data = $request->get_param( 'form_data' );
        if ( ! is_array( $form_data ) ) {
            return [];
        }

        $fields = formgent_get_form_fields( $form );
        foreach ( $fields as $field ) {
            if ( ! is_array( $field ) || 'register' !== ( $field['field_type'] ?? '' ) || empty( $field['name'] ) ) {
                continue;
            }

            $field_name = (string) $field['name'];
            if ( isset( $form_data[$field_name] ) && is_array( $form_data[$field_name] ) ) {
                return [
                    'field'  => $field,
                    'values' => $form_data[$field_name],
                ];
            }
        }

        return [];
    }

    private function get_active_registration( array $registrations ): array {
        if ( empty( $registrations ) ) {
            return [];
        }

        $registration = reset( $registrations );
        return is_array( $registration ) ? $registration : [];
    }

    private function store_custom_user_meta( int $user_id, stdClass $form, WP_REST_Request $request, array $answers, array $registration ): void {
        $custom_meta = $registration['custom_meta'] ?? [];
        if ( ! is_array( $custom_meta ) ) {
            return;
        }

        foreach ( $custom_meta as $meta_row ) {
            if ( ! is_array( $meta_row ) ) {
                continue;
            }

            $meta_key  = isset( $meta_row['meta_key'] ) ? sanitize_key( (string) $meta_row['meta_key'] ) : '';
            $field_key = isset( $meta_row['field'] ) ? (string) $meta_row['field'] : '';
            if ( '' === $meta_key ) {
                continue;
            }

            $meta_value = '';
            if ( '' !== $field_key ) {
                $meta_value = $this->get_field_value_by_mapping_key( $form, $request, $answers, $field_key );
            }
            if ( '' === $meta_value && isset( $meta_row['meta_value'] ) ) {
                $meta_value = $this->normalize_form_data_value( $meta_row['meta_value'] );
            }

            if ( '' !== $meta_value ) {
                update_user_meta( $user_id, $meta_key, sanitize_text_field( $meta_value ) );
            }
        }
    }

    private function get_primary_registration_result( array $results ): array {
        foreach ( $results as $result ) {
            if ( is_array( $result ) && 'register_block' === ( $result['source'] ?? '' ) ) {
                return $result;
            }
        }

        foreach ( $results as $result ) {
            if ( is_array( $result ) && ! empty( $result['success'] ) ) {
                return $result;
            }
        }

        foreach ( $results as $result ) {
            if ( is_array( $result ) ) {
                return $result;
            }
        }

        return [];
    }

    private function get_mapped_field_value( stdClass $form, WP_REST_Request $request, array $answers, array $field_mapping, string $mapping_key ): string {
        if ( empty( $field_mapping[ $mapping_key ] ) ) {
            return '';
        }
        return $this->get_field_value_by_mapping_key( $form, $request, $answers, (string) $field_mapping[ $mapping_key ] );
    }

    private function get_field_value_by_mapping_key( stdClass $form, WP_REST_Request $request, array $answers, string $field_key ): string {
        $value = $this->get_request_form_data_value( $form, $request, $field_key );
        if ( '' !== $value ) {
            return $value;
        }

        return $this->get_answer_value_by_field_key( $answers, $field_key );
    }

    private function get_request_form_data_value( stdClass $form, WP_REST_Request $request, string $field_key ): string {
        $form_data = $request->get_param( 'form_data' );
        if ( ! is_array( $form_data ) ) {
            return '';
        }

        $fields   = formgent_get_form_fields( $form );
        $location = $this->resolve_field_location_by_mapping_key( $fields, $field_key );
        if ( empty( $location ) || ! is_array( $location ) ) {
            return '';
        }

        if ( ! empty( $location['parent_name'] ) && ! empty( $location['child_name'] ) ) {
            $parent_name = (string) $location['parent_name'];
            $child_name  = (string) $location['child_name'];
            $value       = $form_data[ $parent_name ][ $child_name ] ?? null;
            return is_scalar( $value ) ? trim( (string) $value ) : '';
        }

        if ( ! empty( $location['field_name'] ) ) {
            $field_name = (string) $location['field_name'];
            $value      = $form_data[ $field_name ] ?? null;
            return is_scalar( $value ) ? trim( (string) $value ) : '';
        }

        return '';
    }

    private function resolve_field_location_by_mapping_key( array $fields, string $field_key ): array {
        $field_key = trim( $field_key );
        if ( '' === $field_key ) {
            return [];
        }

        if ( preg_match( '/^parent\\[([^\\]]+)\\]child\\[([^\\]]+)\\]$/', $field_key, $matches ) ) {
            $parent_id = (string) $matches[1];
            $child_id  = (string) $matches[2];

            foreach ( $fields as $parent_name => $field ) {
                if ( ! is_array( $field ) || (string) ( $field['id'] ?? '' ) !== $parent_id ) {
                    continue;
                }
                $children = $field['children'] ?? [];
                if ( ! is_array( $children ) ) {
                    continue;
                }
                foreach ( $children as $child_name => $child_field ) {
                    if ( (string) ( $child_field['id'] ?? '' ) === $child_id ) {
                        return [
                            'parent_name' => (string) $parent_name,
                            'child_name'  => (string) $child_name,
                        ];
                    }
                }
            }
        }

        foreach ( $fields as $field_name => $field ) {
            if ( ! is_array( $field ) ) {
                continue;
            }
            if ( (string) ( $field['id'] ?? '' ) === $field_key || (string) $field_name === $field_key ) {
                return [ 'field_name' => (string) $field_name ];
            }

            $children = $field['children'] ?? [];
            if ( ! is_array( $children ) ) {
                continue;
            }
            foreach ( $children as $child_name => $child_field ) {
                if ( (string) ( $child_field['id'] ?? '' ) === $field_key || (string) $child_name === $field_key ) {
                    return [
                        'parent_name' => (string) $field_name,
                        'child_name'  => (string) $child_name,
                    ];
                }
            }
        }

        return [];
    }

    private function get_answer_value_by_field_key( array $answers, string $field_key ): string {
        $normalized_key = $this->normalize_field_key( $field_key );
        if ( '' === $normalized_key || ! isset( $answers[ $normalized_key ] ) ) {
            return '';
        }

        $answer = $answers[ $normalized_key ];
        if ( ! $answer instanceof AnswerFieldDTO ) {
            return '';
        }

        $value = $answer->get_value();
        if ( is_scalar( $value ) ) {
            return trim( (string) $value );
        }

        return '';
    }

    private function normalize_field_key( string $field_key ): string {
        $field_key = trim( $field_key );
        if ( '' === $field_key ) {
            return '';
        }

        // Parsed block values for composite fields (address/name/register) are stored as:
        // parent[{parentId}]child[{childId}] — answers are keyed by childId.
        if ( preg_match( '/^parent\\[[^\\]]+\\]child\\[([^\\]]+)\\]$/', $field_key, $matches ) ) {
            return (string) $matches[1];
        }

        return $field_key;
    }

    private function normalize_form_data_value( $value ): string {
        if ( is_scalar( $value ) ) {
            return trim( (string) $value );
        }

        if ( ! is_array( $value ) ) {
            return '';
        }

        $values = [];
        array_walk_recursive(
            $value,
            static function( $item ) use ( &$values ) {
                if ( ! is_scalar( $item ) ) {
                    return;
                }

                $item = trim( (string) $item );
                if ( '' !== $item ) {
                    $values[] = $item;
                }
            }
        );

        return implode( ', ', $values );
    }

    private function validate_register_text_field( array &$errors, string $field_name, string $value, array $field ): void {
        if ( empty( $field ) ) {
            return;
        }

        $value = trim( $value );
        $label = $this->get_register_field_label( $field, $field_name );

        if ( ! empty( $field['required'] ) && '' === $value ) {
            $errors[$field_name] = sprintf(
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
            $errors[$field_name] = sprintf(
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

        $minimum = $strength_map[$minimum_strength] ?? $strength_map['medium'];
        $level   = $this->get_password_strength_level( $password );
        $actual  = $strength_map[$level] ?? $strength_map['invalid'];

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

    private function get_logged_in_message( array $registration, int $user_id ): string {
        $message = isset( $registration['hide_for_logged_in_message'] )
            ? (string) $registration['hide_for_logged_in_message']
            : '';

        if ( '' === $message ) {
            return esc_html__( 'You are now logged in.', 'formgent' );
        }

        $user = get_user_by( 'id', $user_id );
        if ( ! $user ) {
            return wp_kses_post( $message );
        }

        $message = str_replace(
            '{{user_name}}',
            esc_html( $user->display_name ),
            $message
        );

        $message = str_replace(
            '{{logout_url}}',
            esc_url( wp_logout_url() ),
            $message
        );

        return wp_kses_post( $message );
    }

    private function make_unique_username( string $username ): string {
        $username = trim( $username );
        if ( '' === $username ) {
            return '';
        }

        $base_username = $username;
        $suffix        = 1;

        while ( username_exists( $username ) && $suffix <= 999 ) {
            $username = $base_username . $suffix;
            $suffix++;
        }

        return $username;
    }

    /**
     * Roles that must never be assigned via frontend self-registration.
     */
    private const FORBIDDEN_ROLES = [ 'administrator', 'editor' ];

    private function resolve_user_role( array $registration ): string {
        $role = isset( $registration['user_role'] ) ? sanitize_key( (string) $registration['user_role'] ) : 'subscriber';

        if ( 'custom' === $role ) {
            $custom_role = isset( $registration['custom_role'] ) ? sanitize_key( (string) $registration['custom_role'] ) : '';
            if ( '' !== $custom_role && get_role( $custom_role ) && ! in_array( $custom_role, self::FORBIDDEN_ROLES, true ) ) {
                return $custom_role;
            }
            return 'subscriber';
        }

        if ( in_array( $role, self::FORBIDDEN_ROLES, true ) ) {
            return 'subscriber';
        }

        if ( get_role( $role ) ) {
            return $role;
        }

        return 'subscriber';
    }
}
