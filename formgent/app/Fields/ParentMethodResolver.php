<?php

namespace FormGent\App\Fields;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\AnswerDTO;
use FormGent\WpMVC\RequestValidator\Validator;
use WP_REST_Request;
use stdClass;

trait ParentMethodResolver {
    protected function get_validation_rules( array $field ): array {
        return [];
    }

    public function validate( array $field, WP_REST_Request $wp_rest_request, Validator $validator, stdClass $form ) {
        if ( 'FormGentPro\App\Fields\ParentField' === parent::class ) {
            parent::validate( ...func_get_args() );
        }
    }

    public function get_field_dto( array $field, WP_REST_Request $wp_rest_request, stdClass $form ): AnswerDTO {
        return ( new AnswerDTO() )->set_form_id( $form->ID )->set_field_type( $field['field_type'] )->set_field_name( $field['name'] );
    }
}
