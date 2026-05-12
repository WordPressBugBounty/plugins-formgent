<?php

namespace FormGent\App\Fields\Login;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Summary\Pagination;

trait MethodResolver {

    use Pagination;

    public static function get_key(): string {
        return 'login';
    }

    protected function get_validation_rules( array $field ): array {
        return [];
    }
}
