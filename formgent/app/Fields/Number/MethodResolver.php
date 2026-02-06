<?php

namespace FormGent\App\Fields\Number;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Summary\Pagination;

trait MethodResolver {

    use Pagination;

    public static function get_key(): string {
        return 'number';
    }

    protected function get_validation_rules( array $field ): array {
        $rules = [];

        switch ( $field['format'] ) {
            case 'non_decimal':
                $rules[] = 'integer';
                break;
            default:
                $rules[] = 'numeric';
                break;
        }

        if ( $field['limit'] ) {
            $rules[] = 'min:' . absint( $field['min'] );
            $rules[] = 'max:' . absint( $field['max'] );
        }

        return $rules;
    }
}
