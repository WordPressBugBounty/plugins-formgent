<?php

namespace FormGent\App\Utils;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\ResponseDTO;

class ConditionalLogic {
    private array $conditions;

    private string $logical_type;

    private ResponseDTO $response;

    private array $answers_items;

    /**
     * Determines if the conditions are passed based on the logical type.
     *
     * This method evaluates a set of conditions and determines whether they are
     * satisfied based on the logical type ('or' or 'and'). If the logical type is
     * 'or', the method returns true if at least one condition is valid. If the
     * logical type is 'and', the method returns true only if all conditions are valid.
     *
     * @return bool True if the conditions are passed, false otherwise.
     */
    public function is_passed(): bool {
        if ( 'or' === $this->logical_type ) {
            foreach ( $this->conditions as $condition ) {
                if ( $this->validate_condition( $condition ) ) {
                    return true;
                }
            }
            return false;
        }

        foreach ( $this->conditions as $condition ) {
            if ( ! $this->validate_condition( $condition ) ) {
                return false;
            }
        }

        return true;
    }

    protected function validate_condition( array $condition ): bool {
        $field_value     = formgent_form_preset_field_repository()->transform_value( $condition['field'], $this->answers_items, $this->response, '' );
        $condition_value = $condition['value'];

        switch ( $condition['operator'] ) {
            case '=':
                if ( $this->is_multi_value( $field_value ) ) {
                    return $this->field_value_has_all( $field_value, $condition_value );
                }
                return strval( $field_value ) == strval( $condition_value );

            case '!=':
                if ( $this->is_multi_value( $field_value ) ) {
                    return ! $this->field_value_has_all( $field_value, $condition_value );
                }
                return strval( $field_value ) != strval( $condition_value );

            case 'equal_length':
                return strlen( strval( $field_value ) ) == intval( $condition_value );

            case 'less_then_length':
                return strlen( strval( $field_value ) ) < intval( $condition_value );

            case 'greater_then_length':
                return strlen( strval( $field_value ) ) > intval( $condition_value );

            case 'contains':
                if ( empty( $condition_value ) ) {
                    return true;
                }

                if ( empty( $field_value ) ) {
                    return false;
                }

                if ( ! $this->is_multi_value( $field_value ) ) {
                    return  strpos( strtolower( strval( $field_value ) ), strtolower( strval( $condition_value ) ) ) !== false;
                }

                return $this->field_value_has_any( $field_value, $condition_value );
            
            case 'doesNotContain':
                if ( empty( $condition_value ) ) {
                    return true;
                }

                if ( empty( $field_value ) ) {
                    return false;
                }

                if ( ! $this->is_multi_value( $field_value ) ) {
                    return  strpos( strtolower( strval( $field_value ) ), strtolower( strval( $condition_value ) ) ) === false;
                }

                return ! $this->field_value_has_any( $field_value, $condition_value );
            
            case 'regex':
                $pattern = '/' . str_replace( '/', '\/', strval( $condition_value ) ) . '/';
                try {
                    return @preg_match( $pattern, strval( $field_value ) ) === 1;
                } catch ( \Exception $e ) {
                    return false;
                }
            
            case 'less_than':
            case 'less_then':
                return floatval( $field_value ) < floatval( $condition_value );

            case 'greater_than':
            case 'greater_then':
                return floatval( $field_value ) > floatval( $condition_value );

            case 'between':
                $range = array_map( 'floatval', array_map( 'trim', explode( ',', $condition_value ) ) );
                return count( $range ) === 2 && floatval( $field_value ) >= $range[0] && floatval( $field_value ) <= $range[1];

            case 'null':
                return empty( $field_value );

            default:
                return false;
        }
    }

    private function parse_rule_values( $value ): array {
        if ( is_array( $value ) ) {
            return array_values( array_filter( array_map( 'strval', $value ), 'strlen' ) );
        }

        return array_values(
            array_filter(
                array_map( 'trim', explode( ',', strval( $value ) ) ),
                static function( $item ) {
                    return '' !== $item;
                }
            )
        );
    }

    private function normalize_multi_value( $value ): array {
        if ( is_string( $value ) ) {
            $decoded = json_decode( $value, true );

            if ( is_array( $decoded ) ) {
                $value = $decoded;
            }
        }

        if ( ! is_array( $value ) ) {
            return [];
        }

        $values = [];
        $walk   = static function( $item ) use ( &$values, &$walk ) {
            if ( is_array( $item ) ) {
                foreach ( $item as $key => $child ) {
                    if ( false === $child || null === $child || '' === $child ) {
                        continue;
                    }

                    if ( ! is_int( $key ) ) {
                        $values[] = strval( $key );
                    }

                    if ( true === $child ) {
                        continue;
                    }

                    $walk( $child );
                }
                return;
            }

            if ( null !== $item && '' !== $item ) {
                $values[] = strval( $item );
            }
        };

        $walk( $value );

        return array_values( array_unique( $values ) );
    }

    private function is_multi_value( $value ): bool {
        if ( is_array( $value ) ) {
            return true;
        }

        if ( ! is_string( $value ) ) {
            return false;
        }

        return is_array( json_decode( $value, true ) );
    }

    private function field_value_has_all( $field_value, $condition_value ): bool {
        $field_values = $this->normalize_multi_value( $field_value );
        $rule_values  = $this->parse_rule_values( $condition_value );

        if ( empty( $rule_values ) ) {
            return false;
        }

        foreach ( $rule_values as $value ) {
            if ( ! in_array( strval( $value ), $field_values, true ) ) {
                return false;
            }
        }

        return true;
    }

    private function field_value_has_any( $field_value, $condition_value ): bool {
        $field_values = $this->normalize_multi_value( $field_value );

        foreach ( $this->parse_rule_values( $condition_value ) as $value ) {
            if ( in_array( strval( $value ), $field_values, true ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the value of conditions
     *
     * @return array
     */
    public function get_conditions(): array {
        return $this->conditions;
    }

    /**
     * Set the value of conditions
     *
     * @param array $conditions 
     *
     * @return self
     */
    public function set_conditions( array $conditions ): self {
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * Get the value of logical_type
     *
     * @return string
     */
    public function get_logical_type(): string {
        return $this->logical_type;
    }

    /**
     * Set the value of logical_type
     *
     * @param string $logical_type 
     *
     * @return self
     */
    public function set_logical_type( string $logical_type ): self {
        $this->logical_type = $logical_type;

        return $this;
    }

    /**
     * Get the value of response
     *
     * @return ResponseDTO
     */
    public function get_response(): ResponseDTO {
        return $this->response;
    }

    /**
     * Set the value of response
     *
     * @param ResponseDTO $response 
     *
     * @return self
     */
    public function set_response( ResponseDTO $response ): self {
        $this->response = $response;

        return $this;
    }

    /**
     * Get the value of answers_items
     *
     * @return array
     */
    public function get_answers_items(): array {
        return $this->answers_items;
    }

    /**
     * Set the value of answers_items
     *
     * @param array $answers_items 
     *
     * @return self
     */
    public function set_answers_items( array $answers_items ): self {
        $this->answers_items = $answers_items;

        return $this;
    }
}
