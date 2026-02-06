<?php

namespace FormGent\App\Fields\GDPR;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Summary\Pagination;

trait MethodResolver {
    
    use Pagination;

    public static function get_key(): string {
        return 'gdpr';
    }

    protected function get_validation_rules( array $field ): array {
        return ['integer', 'accepted:0,1'];
    }
}
