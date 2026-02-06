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
                return strval( $field_value ) == strval( $condition_value );

            case '!=':
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

                $array_value = json_decode( $field_value, true );

                if ( is_null( $array_value ) ) {
                    return  strpos( strtolower( strval( $field_value ) ), strtolower( strval( $condition_value ) ) ) !== false;
                }

                return in_array( $condition_value, $array_value, true );
            
            case 'doesNotContain':
                if ( empty( $condition_value ) ) {
                    return true;
                }

                if ( empty( $field_value ) ) {
                    return false;
                }

                $array_value = json_decode( $field_value, true );

                if ( is_null( $array_value ) ) {
                    return  strpos( strtolower( strval( $field_value ) ), strtolower( strval( $condition_value ) ) ) === false;
                }

                return ! in_array( $condition_value, $array_value, true );
            
            case 'regex':
                return preg_match( '/' . strval( $condition_value ) . '/', strval( $field_value ) ) === 1;
            
            case 'null':
                return empty( $field_value );
            
            default:
                return false;
        }
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