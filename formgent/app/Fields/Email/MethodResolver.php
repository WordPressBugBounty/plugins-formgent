<?php

namespace FormGent\App\Fields\Email;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Summary\Pagination;
use FormGent\WpMVC\RequestValidator\Validator;
use WP_REST_Request;
use stdClass;

trait MethodResolver {

    use Pagination;

    public static function get_key(): string {
        return 'email';
    }

    protected function get_validation_rules( array $field ): array {
        $rules = ['string', 'email'];

        if ( $field['character_limit'] ) {
            $rules[] = "max:" . absint( $field['limit'] );
        }

        return $rules;
    }

    public function validate( array $field, WP_REST_Request $wp_rest_request, Validator $validator, stdClass $form ) {
        parent::validate( $field, $wp_rest_request, $validator, $form );

        $is_admin_edit = (bool) $wp_rest_request->get_param( '_formgent_admin_edit' );

        // Email confirmation is a user-facing anti-typo UX feature.
        // When admins edit an entry, we skip confirmation validation.
        if ( ! $is_admin_edit && $field['enable_confirmation_field'] ) {
            $validator->validate(
                [
                    $field['name'] => 'confirmed'
                ]
            );
        }
    }
}
