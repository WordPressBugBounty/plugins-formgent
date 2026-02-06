<?php //phpcs:ignore Internal.Exception

namespace FormGent\App\Fields\MultipleChoice;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\RequestValidator\Validator;
use FormGent\App\DTO\AnswerDTO;
use FormGent\App\Summary\MultiChoice;
use FormGent\WpMVC\Exceptions\Exception;
use FormGent\WpMVC\Helpers\Helpers;
use stdClass;
use WP_REST_Request;

trait MethodResolver {

    use MultiChoice;

    public static function get_key(): string {
        return 'multiple-choice';
    }

    protected function get_validation_rules( array $field ): array {
        return ['array'];
    }

    public function get_field_dto( array $field, WP_REST_Request $wp_rest_request, stdClass $form ): AnswerDTO {
        $values = $wp_rest_request->get_param( $field['name'] );

        if ( isset( $values['formgent_other'] ) ) {
            $other = $values['formgent_other'];
            unset( $values['formgent_other'] );

            $values   = array_keys( $values );
            $values[] = $other;
        } else {
            $values = array_keys( $values );
        }

        return ( new AnswerDTO() )->set_form_id( $form->ID )->set_field_type( $field['field_type'] )->set_field_name( $field['name'] )->set_value( $values );
    }

    public function validate( array $field, WP_REST_Request $wp_rest_request, Validator $validator, stdClass $form ) {
        $rules = $this->get_validation_rules( $field );

        $validator->validate(
            [
                $field['name'] => implode( '|', $rules ),
            ]
        );

        $is_required = isset( $field["required"] ) && $field["required"];
        $values      = $wp_rest_request->get_param( $field['name'] );

        if ( '1' === $is_required ) {
            throw (new Exception())->set_messages(
                [
                    $field['name'] => [
                        sprintf( "The %s field is required.", $field['name'] )
                    ]
                ]
            );
        }

        if ( empty( $values ) ) {
            return;
        }

        if ( ! Helpers::is_one_level_array( $values ) ) {
            throw (new Exception())->set_messages(
                [
                    $field['name'] => [
                        "Something was wrong"
                    ]
                ]
            );
        }

        if ( array_unique( $values ) !== $values ) {
            throw (new Exception())->set_messages(
                [
                    $field['name'] => [
                        sprintf( "The %s field does not allow the same value multiple times", $field['name'] )
                    ]
                ]
            );
        }

        if ( $field['choice_limit'] && count( $values ) > $field['choice_limit_item'] ) {
            throw (new Exception())->set_messages(
                [
                    $field['name'] => sprintf( "Can select maximum %d items", $field['choice_limit_item'] )
                ]
            );
        }

        if ( isset( $values['formgent_other'] ) && strlen( $values['formgent_other'] ) > 255 ) {
            throw (new Exception())->set_messages(
                [
                    $field['name'] => "Other option value must be less then 255 characters"
                ]
            );
        }

        unset( $values['formgent_other'] );

        $options = wp_list_pluck( $field['options'], 'value' );

        if ( ! empty( array_diff( array_keys( $values ), $options ) ) ) {
            throw (new Exception())->set_messages(
                [
                    $field['name'] => [
                        sprintf( "The value of %s must be between %s", $field['name'], implode( ',', $options ) )
                    ]
                ]
            );
        }
    }
}
