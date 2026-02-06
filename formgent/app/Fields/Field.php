<?php

namespace FormGent\App\Fields;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\AnswerDTO;
use FormGent\App\EnumeratedList\FormType;
use FormGent\App\Repositories\AnswerRepository;
use FormGent\WpMVC\RequestValidator\Validator;
use stdClass;
use WP_REST_Request;

abstract class Field {
    public AnswerRepository $answer_repository;

    public $has_children = false;

    public function __construct( AnswerRepository $answer_repository ) {
        $this->answer_repository = $answer_repository;
    }

    abstract public static function get_key(): string;
    
    abstract public function get_summary( stdClass $form, array $field, int $page = 0, int $per_page = 0 );

    public static function get_supported_form_types(): array {
        return [FormType::GENERAL, FormType::CONVERSATIONAL];
    }

    protected function get_validation_rules( array $field ): array {
        return [];
    }

    public function validate( array $field, WP_REST_Request $wp_rest_request, Validator $validator, stdClass $form ) {
        $rules = $this->get_validation_rules( $field );

        if ( isset( $field["required"] ) && $field["required"] ) {
            $rules[] = 'required';
        }

        if ( ! empty( $rules ) ) {

            $validator->validate(
                [
                    $field['name'] => implode( '|', $rules ),
                ]
            );
        }
    }

    public function get_field_dto( array $field, WP_REST_Request $wp_rest_request, stdClass $form ): AnswerDTO {
        return ( new AnswerDTO() )->set_form_id( $form->ID )->set_field_type( $field['field_type'] )->set_field_name( $field['name'] )->set_value( $wp_rest_request->get_param( $field['name'] ) );
    }
}