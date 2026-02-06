<?php
namespace FormGent\App\Utils;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\AnswerFieldDTO;

/**
 * Resolve field references to display-ready values.
 */
class FieldValueResolver {
    /**
     * Resolve a field reference using front-end form data (context.data).
     *
     * @param array  $form_data    Reactive form data array.
     * @param array  $form_fields  Parsed form field definitions.
     * @param string $reference    Field reference (e.g. "email" or "group.child").
     *
     * @return string|null
     */
    public function resolve_from_form_data( array $form_data, array $form_fields, string $reference ): ?string {
        $value = $this->traverse_array( $form_data, $reference );

        if ( null === $value || is_array( $value ) ) {
            return null;
        }

        $field_definition = $this->locate_field_definition( $form_fields, $reference );

        return $this->format_value( $value, $field_definition );
    }

    /**
     * Resolve a field reference using AnswerFieldDTOs.
     *
     * @param AnswerFieldDTO[] $answers       Flattened answers array.
     * @param array            $form_fields   Parsed form field definitions.
     * @param string           $reference     Field reference.
     *
     * @return string|null
     */
    public function resolve_from_answers( array $answers, array $form_fields, string $reference ): ?string {
        $segments = explode( '.', $reference );
        $target   = $answers;

        foreach ( $segments as $segment ) {
            if ( isset( $target[ $segment ] ) && $target[ $segment ] instanceof AnswerFieldDTO ) {
                $target = $target[ $segment ]->get_value();
            } else {
                return null;
            }
        }

        if ( is_array( $target ) ) {
            return null;
        }

        $field_definition = $this->locate_field_definition( $form_fields, $reference );

        return $this->format_value( $target, $field_definition );
    }

    /**
     * Locate form field definition by reference.
     *
     * @param array  $form_fields Parsed form field definitions.
     * @param string $reference   Field reference.
     *
     * @return array
     */
    private function locate_field_definition( array $form_fields, string $reference ): array {
        $segments = explode( '.', $reference );
        $field    = $form_fields;

        foreach ( $segments as $segment ) {
            if ( isset( $field[ $segment ] ) ) {
                $field = $field[ $segment ];
                continue;
            }

            if ( isset( $field['children'][ $segment ] ) ) {
                $field = $field['children'][ $segment ];
                continue;
            }

            return [];
        }

        return is_array( $field ) ? $field : [];
    }

    /**
     * Traverse nested array using dot notation.
     */
    private function traverse_array( array $data, string $reference ) {
        $segments = explode( '.', $reference );
        $value    = $data;

        foreach ( $segments as $segment ) {
            if ( is_array( $value ) && array_key_exists( $segment, $value ) ) {
                $value = $value[ $segment ];
                continue;
            }

            return null;
        }

        return $value;
    }

    /**
     * Format raw value according to field type.
     *
     * @param mixed $value
     * @param array $definition
     *
     * @return string
     */
    private function format_value( $value, array $definition ): string {
        if ( ! isset( $definition['field_type'] ) ) {
            return (string) $value;
        }

        switch ( $definition['field_type'] ) {
            case 'single-choice':
            case 'dropdown':
                return $this->resolve_choice_label( $definition, $value );

            case 'multiple-choice':
                if ( is_array( $value ) ) {
                    $labels = array_map(
                        function ( $item ) use ( $definition ) {
                            return $this->resolve_choice_label( $definition, $item );
                        },
                        $value
                    );

                    return implode( ', ', array_filter( $labels ) );
                }
                return $this->resolve_choice_label( $definition, $value );

            case 'rating':
            case 'number':
                return (string) $value;

            default:
                return (string) $value;
        }
    }

    /**
     * Map option value to its label.
     */
    private function resolve_choice_label( array $definition, $value ): string {
        if ( empty( $definition['options'] ) ) {
            return (string) $value;
        }

        foreach ( $definition['options'] as $option ) {
            if ( isset( $option['value'] ) && $option['value'] == $value ) {
                return isset( $option['label'] ) ? (string) $option['label'] : (string) $value;
            }
        }

        return (string) $value;
    }
}