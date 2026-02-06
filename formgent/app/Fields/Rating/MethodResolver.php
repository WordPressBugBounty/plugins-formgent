<?php

namespace FormGent\App\Fields\Rating;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Summary\Pagination;

trait MethodResolver {

    use Pagination;

    public static function get_key(): string {
        return 'rating';
    }

    protected function get_validation_rules( array $field ): array {
        return [
            'integer',
            'max:' . absint( $field['rating_limit'] )
        ];
    }
}
