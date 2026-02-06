<?php

namespace FormGent\App\Fields\InputMasking;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Summary\Pagination;

trait MethodResolver {
    
    use Pagination;

    public static function get_key(): string {
        return 'input-masking';
    }

    protected function get_validation_rules( array $field ): array {
        return ['string', 'min:1', 'max:250'];
    }
}
