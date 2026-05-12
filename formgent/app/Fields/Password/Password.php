<?php

namespace FormGent\App\Fields\Password;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\AnswerDTO;
use FormGent\App\Fields\Field;
use stdClass;
use WP_REST_Request;

class Password extends Field {
    use MethodResolver;

    public function get_field_dto( array $field, WP_REST_Request $wp_rest_request, stdClass $form ): AnswerDTO {
        $raw_password = (string) $wp_rest_request->get_param( $field['name'] );

        $value = '';
        if ( '' !== $raw_password ) {
            $value = wp_hash_password( $raw_password );
        }

        return ( new AnswerDTO() )
            ->set_form_id( $form->ID )
            ->set_field_type( $field['field_type'] )
            ->set_field_name( $field['name'] )
            ->set_value( $value );
    }
}

