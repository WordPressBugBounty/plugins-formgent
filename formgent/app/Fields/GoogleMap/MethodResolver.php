<?php

namespace FormGent\App\Fields\GoogleMap;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\AnswerDTO;
use FormGent\App\Summary\Pagination;
use FormGent\WpMVC\RequestValidator\Validator;
use WP_REST_Request;
use stdClass;

trait MethodResolver {

    use Pagination;

    public static function get_key(): string {
        return 'google-map';
    }

    public function validate( array $field, WP_REST_Request $wp_rest_request, Validator $validator, stdClass $form ) {
        parent::validate( $field, $wp_rest_request, $validator, $form );

        //TODO: need backend validation
    }

    public function get_field_dto( array $field, WP_REST_Request $wp_rest_request, stdClass $form ): AnswerDTO {
        $values = $wp_rest_request->get_param( $field['name'] );

        $values = [
            'map'     => [
                'lat' => isset( $values['map']['lat'] ) ? $this->sanitize_lat_lng( $values['map']['lat'], 'lat' ) : null,
                'lng' => isset( $values['map']['lng'] ) ? $this->sanitize_lat_lng( $values['map']['lng'], 'lng' ) : null,
            ],
            'address' => $values['address'] ? sanitize_text_field( $values['address'] ) : null,
        ];

        return ( new AnswerDTO() )->set_form_id( $form->ID )->set_field_type( $field['field_type'] )->set_field_name( $field['name'] )->set_value( $values );
    }

    protected function sanitize_lat_lng( $value, $type = 'lat' ) {
        $value = floatval( $value );

        if ( 'lat' === $type ) {
            if ( $value < -90 || $value > 90 ) {
                return null;
            }
        } elseif ( 'lng' === $type || 'lon' === $type ) {
            if ( $value < -180 || $value > 180 ) {
                return null;
            }
        }

        return $value;
    }
}
