<?php

namespace FormGent\App\Fields\RangeSlider;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Summary\Pagination;
use FormGent\WpMVC\RequestValidator\Validator;
use FormGent\WpMVC\Exceptions\Exception;
use stdClass;
use WP_REST_Request;

trait MethodResolver {

    use Pagination;

    public static function get_key(): string {
        return 'range-slider';
    }

    protected function get_validation_rules( array $field ): array {
        $rules = [];
    
        // Default to number type if slider_type is not set (like Number field doesn't check format)
        $slider_type = isset( $field['slider_type'] ) ? $field['slider_type'] : 'number';
    
        if ( $slider_type === 'number' ) {
            $rules[] = 'numeric';
            
            // Validate min/max only if both exist and are numeric
            if ( isset( $field['min_value'] ) && isset( $field['max_value'] ) 
                 && is_numeric( $field['min_value'] ) && is_numeric( $field['max_value'] ) ) {
                $rules[] = 'min:' . floatval( $field['min_value'] );
                $rules[] = 'max:' . floatval( $field['max_value'] );
            }
        } else {
            // Text type: basic string validation (detailed validation in validate() method)
            $rules[] = 'string';
        }
        
        return $rules;
    }

    public function validate( array $field, WP_REST_Request $wp_rest_request, Validator $validator, stdClass $form ) {
        $rules = $this->get_validation_rules( $field );

        if ( isset( $field["required"] ) && $field["required"] ) {
            $rules[] = 'required';
        }

        // For text type, validate against text_options manually to handle edge cases
        if ( isset( $field['slider_type'] ) && $field['slider_type'] === 'text' 
             && isset( $field['text_options'] ) && is_array( $field['text_options'] ) && ! empty( $field['text_options'] ) ) {
            
            $value = $wp_rest_request->get_param( $field['name'] );
            
            // Only validate if value is provided (required check is handled above)
            if ( $value !== null && $value !== '' ) {
                // Get all valid option values (handle both value and label)
                $valid_values = [];
                foreach ( $field['text_options'] as $option ) {
                    if ( isset( $option['value'] ) && $option['value'] !== '' ) {
                        $valid_values[] = $option['value'];
                    }
                    // Also allow label as valid value (for backward compatibility)
                    if ( isset( $option['label'] ) && $option['label'] !== '' && ! in_array( $option['label'], $valid_values, true ) ) {
                        $valid_values[] = $option['label'];
                    }
                }
                
                // Check if value exists in valid options (handles commas, special chars, etc.)
                if ( ! empty( $valid_values ) && ! in_array( $value, $valid_values, true ) ) {
                    throw (new Exception())->set_messages(
                        [
                            $field['name'] => [
                                sprintf( "The selected value for %s is invalid.", $field['name'] )
                            ]
                        ]
                    );
                }
            }
        }

        // Apply standard validation rules
        if ( ! empty( $rules ) ) {
            $validator->validate(
                [
                    $field['name'] => implode( '|', $rules ),
                ]
            );
        }
    }

    protected function get_field_summary( stdClass $form, array $field, int $page = 0, int $per_page = 0 ) {
        $answers = $this->get_answers( $form, $field, $page, $per_page );
        
        // For text type, convert values to labels
        if ( isset( $field['slider_type'] ) && $field['slider_type'] === 'text' 
             && isset( $field['text_options'] ) && is_array( $field['text_options'] ) && ! empty( $field['text_options'] ) ) {
            
            // Ensure $answers is an array
            if ( ! is_array( $answers ) ) {
                $answers = is_object( $answers ) ? (array) $answers : [];
            }
            
            foreach ( $answers as $answer ) {
                // Handle both object and array formats
                $value = is_object( $answer ) ? $answer->value : ( isset( $answer['value'] ) ? $answer['value'] : '' );
                
                if ( $value !== '' && $value !== null ) {
                    // Find the label for this value
                    foreach ( $field['text_options'] as $option ) {
                        // Match by value first
                        if ( isset( $option['value'] ) && $option['value'] === $value ) {
                            $label = isset( $option['label'] ) ? $option['label'] : $value;
                            if ( is_object( $answer ) ) {
                                $answer->value = $label;
                            } else {
                                $answer['value'] = $label;
                            }
                            break;
                        }
                        // Fallback: match by label (for backward compatibility)
                        if ( isset( $option['label'] ) && $option['label'] === $value ) {
                            if ( is_object( $answer ) ) {
                                $answer->value = $option['label'];
                            } else {
                                $answer['value'] = $option['label'];
                            }
                            break;
                        }
                    }
                }
            }
        }
        
        return $answers;
    }
}