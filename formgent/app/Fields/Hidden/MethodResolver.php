<?php

namespace FormGent\App\Fields\Hidden;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Summary\Pagination;

trait MethodResolver {
    use Pagination;

    public static function get_key(): string {
        return 'hidden';
    }

    protected function get_validation_rules( array $field ): array {
        return ['string'];
    }
}
