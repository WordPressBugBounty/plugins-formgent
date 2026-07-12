<?php

namespace FormGent\App\Fields\Dropdown;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\AnswerDTO;
use FormGent\App\Fields\SingleChoice\MethodResolver as SingleChoiceMethodResolver;
use FormGent\WpMVC\Exceptions\Exception;
use FormGent\WpMVC\Helpers\Helpers;
use FormGent\WpMVC\RequestValidator\Validator;
use stdClass;
use WP_REST_Request;

trait MethodResolver {

    use SingleChoiceMethodResolver;

    public static function get_key(): string {
        return 'dropdown';
    }

    protected function get_validation_rules( array $field, bool $force_multi_select = false ): array {
        return ( $force_multi_select || $this->is_multi_select_enabled( $field ) )
            ? [ 'array' ]
            : [ 'string|max:255' ];
    }

    public function get_field_dto( array $field, WP_REST_Request $wp_rest_request, stdClass $form ): AnswerDTO {
        $value = $wp_rest_request->get_param( $field['name'] );

        if ( $this->should_treat_as_multi_select( $field, $wp_rest_request ) ) {
            $value = $this->normalize_multi_select_values( $value );
        }

        return ( new AnswerDTO() )
            ->set_form_id( $form->ID )
            ->set_field_type( $field['field_type'] )
            ->set_field_name( $field['name'] )
            ->set_value( $value );
    }

    public function validate( array $field, WP_REST_Request $wp_rest_request, Validator $validator, stdClass $form ) {
        if ( ! $this->should_treat_as_multi_select( $field, $wp_rest_request ) ) {
            $rules = $this->get_validation_rules( $field );

            if ( isset( $field['required'] ) && $field['required'] ) {
                $rules[] = 'required';
            }

            $validator->validate(
                [
                    $field['name'] => implode( '|', $rules ),
                ]
            );

            return;
        }

        $validator->validate(
            [
                $field['name'] => implode( '|', $this->get_validation_rules( $field, true ) ),
            ]
        );

        $values      = $this->normalize_multi_select_values( $wp_rest_request->get_param( $field['name'] ) );
        $is_required = ! empty( $field['required'] );

        if ( $is_required && empty( $values ) ) {
            throw ( new Exception() )->set_messages(
                [
                    $field['name'] => [
                        sprintf( 'The %s field is required.', $field['name'] ),
                    ],
                ]
            );
        }

        if ( empty( $values ) ) {
            return;
        }

        if ( ! Helpers::is_one_level_array( $values ) ) {
            throw ( new Exception() )->set_messages(
                [
                    $field['name'] => [
                        'Something was wrong',
                    ],
                ]
            );
        }

        if ( array_unique( $values ) !== $values ) {
            throw ( new Exception() )->set_messages(
                [
                    $field['name'] => [
                        sprintf( 'The %s field does not allow the same value multiple times', $field['name'] ),
                    ],
                ]
            );
        }

        $options = $this->get_normalized_option_values( $field );

        if ( ! empty( array_diff( $values, $options ) ) ) {
            throw ( new Exception() )->set_messages(
                [
                    $field['name'] => [
                        sprintf( 'The value of %s must be between %s', $field['name'], implode( ',', $options ) ),
                    ],
                ]
            );
        }
    }

    private function is_multi_select_enabled( array $field ): bool {
        return ! empty( $field['allow_multi_select'] );
    }

    private function should_treat_as_multi_select( array $field, WP_REST_Request $wp_rest_request ): bool {
        if ( $this->is_multi_select_enabled( $field ) ) {
            return true;
        }

        return ! empty( $wp_rest_request->get_param( '_formgent_admin_edit' ) )
            && is_array( $wp_rest_request->get_param( $field['name'] ) );
    }

    private function normalize_multi_select_values( $value ): array {
        if ( is_string( $value ) ) {
            $decoded = json_decode( $value, true );

            if ( is_array( $decoded ) ) {
                $value = $decoded;
            } elseif ( false !== strpos( $value, ',' ) ) {
                $value = array_map( 'trim', explode( ',', $value ) );
            }
        }

        if ( ! is_array( $value ) ) {
            $value = empty( $value ) ? [] : [ $value ];
        }

        $value = array_map(
            static function( $item ) {
                return is_scalar( $item ) ? sanitize_text_field( (string) $item ) : '';
            },
            $value
        );

        return array_values(
            array_filter(
                $value,
                static function( $item ) {
                    return '' !== $item;
                }
            )
        );
    }

    private function get_normalized_option_values( array $field ): array {
        $normalized = [];

        foreach ( $field['options'] ?? [] as $index => $option ) {
            $label = '';
            $value = '';

            if ( is_array( $option ) ) {
                $label = isset( $option['label'] ) ? (string) $option['label'] : '';
                $value = isset( $option['value'] ) ? (string) $option['value'] : '';
            } elseif ( is_scalar( $option ) ) {
                $label = (string) $option;
            }

            $value = sanitize_text_field( $value );

            if ( '' === $value ) {
                $value = sanitize_title( wp_strip_all_tags( $label ) );
            }

            if ( '' === $value ) {
                $value = 'option_' . $index;
            }

            $normalized[] = $value;
        }

        return array_values( array_unique( $normalized ) );
    }
}
