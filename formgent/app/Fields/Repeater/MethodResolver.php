<?php //phpcs:ignore Internal.Exception

namespace FormGent\App\Fields\Repeater;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Fields\Field;
use FormGent\App\DTO\AnswerDTO;
use FormGent\App\Summary\Pagination;
use FormGent\WpMVC\RequestValidator\Validator;
use stdClass;
use WP_REST_Request;
use FormGent\WpMVC\Exceptions\Exception;

trait MethodResolver {

    use Pagination;

    public static function get_key(): string {
        return 'repeater';
    }

    protected function get_validation_rules( array $field ): array {
        return ['array'];
    }

    public function validate( array $field, WP_REST_Request $wp_rest_request, Validator $validator, stdClass $form ) {
        $answer_items = $wp_rest_request->get_param( $field['name'] );

        $repeater_request = new WP_REST_Request( 'POST', '/' );
        $repeater_request->set_body_params(
            [
                'id' => $wp_rest_request->get_param( 'id' )
            ]
        );

        $validator->wp_rest_request = $repeater_request;
        $errors                     = [];

        foreach ( $answer_items as $key => $answer_item ) {
            $repeater_request->set_body_params( $answer_item );
            $field_errors = [];
            foreach ( $field['children'] as $child_field ) {
                try {
                    $field_handler = formgent_field_handler( $child_field['field_type'] );
                    $field_handler->validate( $child_field, $repeater_request, $validator, $form );

                    if ( $field_handler->has_children ) {
                        $children                   = $this->validate_children( $form, $validator, $repeater_request, $child_field );
                        $field_errors               = array_merge( $field_errors, $children );
                        $validator->wp_rest_request = $repeater_request;
                    }
                } catch ( Exception $ex ) {
                    $validator->errors = [];
                    $field_errors      = array_merge( $field_errors, $ex->get_messages() );
                }

            }
            if ( ! empty( $field_errors ) ) {
                $errors[ $key ] = $field_errors;
            }
        }

        $validator->wp_rest_request = $wp_rest_request;

        if ( ! empty( $errors ) ) {
            throw ( new Exception( '', 422 ) )->set_messages( $errors );
        }
    }

    public function get_field_dto( array $field, WP_REST_Request $wp_rest_request, stdClass $form ): AnswerDTO {
        $answer_items = $wp_rest_request->get_param( $field['name'] );

        $repeater_request = new WP_REST_Request( 'POST', '/' );
        $repeater_request->set_body_params(
            [
                'id' => $wp_rest_request->get_param( 'id' ),
            ]
        );

        $values = [];

        foreach ( $answer_items as $key => $answer_item ) {
            $repeater_request->set_body_params( $answer_item );

            foreach ( $field['children'] as $child_field ) {
                if ( empty( $repeater_request->get_param( $child_field['name'] ) ) ) {
                    continue;
                }

                $field_handler = formgent_field_handler( $child_field['field_type'] );
                $dto           = $field_handler->get_field_dto( $child_field, $repeater_request, $form );

                if ( $field_handler->has_children ) {
                    $children = $this->get_children_dtos( $form, $repeater_request, $child_field );
                    $dto->set_value( $children );
                }

                $values[ $key ][ $dto->get_field_name() ] = $dto->get_value();
            }
        }

        return ( new AnswerDTO() )->set_form_id( $form->ID )->set_field_type( $field['field_type'] )->set_field_name( $field['name'] )->set_value( $values );
    }

    public function validate_children( stdClass $form, Validator $validator, WP_REST_Request $request, array $parent_field ): array {
        return $this->process_children_fields(
            $form, $request, $parent_field, function( Field $field_handler, array $field, WP_REST_Request $child_request, ?Validator $validator = null ) use ( $form ) {
                $field_handler->validate( $field, $child_request, $validator, $form );
                return null;
            },
            $validator
        ) ?? [];
    }

    public function get_children_dtos( stdClass $form, WP_REST_Request $request, array $parent_field ): array {
        return $this->process_children_fields(
            $form, $request, $parent_field, function ( Field $field_handler, array $field, WP_REST_Request $child_request ) use ( $form ) {
                return $field_handler->get_field_dto( $field, $child_request, $form )->get_value();
            }
        ) ?? [];
    }

    /**
     * Shared handler for both validate_children and get_children_dtos
     */
    private function process_children_fields(
        stdClass $form,
        WP_REST_Request $request,
        array $parent_field,
        callable $callback,
        ?Validator $validator = null
    ): ?array {
        $form_data = $request->get_param( $parent_field['name'] );

        if ( ! is_array( $form_data ) ) {
            return null;
        }

        $child_request = new WP_REST_Request( 'POST', '/' );
        $child_request->set_body_params( $form_data );

        if ( $validator ) {
            $validator->wp_rest_request = $child_request;
        }

        $fields            = $parent_field['children'];
        $registered_fields = formgent_config( 'fields' );

        $results = [];

        foreach ( $form_data as $field_name => $field_data ) {
            if ( empty( $fields[ $field_name ] ) ) {
                continue;
            }

            $field = $fields[ $field_name ];

            if ( empty( $registered_fields[ $field['field_type'] ]['allowed_in_response'] ) ) {
                continue;
            }

            $field_handler = formgent_field_handler( $field['field_type'] );

            if ( ! in_array( $form->form_type, $field_handler::get_supported_form_types(), true ) ) {
                continue;
            }

            try {
                $result = $callback( $field_handler, $field, $child_request, $validator );
                if ( $result !== null ) {
                    $results[ $field['name'] ] = $result;
                }
            } catch ( Exception $e ) {
                if ( $validator ) {
                    $results = array_merge( $results, $e->get_messages() );
                }
            }
        }

        return $results;
    }
}