<?php

namespace FormGent\App\Fields\DatePicker;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Summary\Pagination;

trait MethodResolver {
    
    use Pagination;

    public static function get_key(): string {
        return 'date-picker';
    }

    protected function get_validation_rules( array $field ): array {
        return [];
    }
}
