<?php

namespace FormGent\App\Fields\Text;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Summary\Pagination;

trait MethodResolver {
    
    use Pagination;

    public static function get_key(): string {
        return 'text';
    }

    protected function get_validation_rules( array $field ): array {
        $rules = ['string'];

        if ( $field['character_limit'] ) {
            $rules[] = "max:" . absint( $field['limit'] );
        }

        return $rules;
    }
}
