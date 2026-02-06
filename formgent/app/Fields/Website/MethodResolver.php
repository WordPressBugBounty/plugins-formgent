<?php

namespace FormGent\App\Fields\Website;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Summary\Pagination;

trait MethodResolver {

    use Pagination;
    
    public static function get_key(): string {
        return 'website';
    }

    protected function get_validation_rules( array $field ): array {
        $rules = ['string', 'url'];

        if ( $field['character_limit'] ) {
            $rules[] = "max:" . absint( $field['limit'] );
        }

        return $rules;
    }
}
