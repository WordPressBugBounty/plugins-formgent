<?php

namespace FormGent\App\Fields\SingleChoice;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\RequestValidator\Validator;
use FormGent\App\Summary\SingleChoice;
use stdClass;
use WP_REST_Request;

trait MethodResolver {
    
    use SingleChoice;

    public static function get_key(): string {
        return 'single-choice';
    }

    protected function get_validation_rules( array $field ): array {
        return ['string|max:255'];
    }

    public function validate( array $field, WP_REST_Request $wp_rest_request, Validator $validator, stdClass $form ) {
        $rules = $this->get_validation_rules( $field );

        if ( isset( $field["required"] ) && $field["required"] ) {
            $rules[] = 'required';
        }

        // $rules[] = 'accepted:' . implode( ',', wp_list_pluck( $field['options'], 'value' ) );
    
        $validator->validate(
            [
                $field['name'] => implode( '|', $rules ),
            ]
        );
    }
}
