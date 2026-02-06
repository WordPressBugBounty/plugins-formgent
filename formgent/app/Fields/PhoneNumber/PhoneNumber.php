<?php

namespace FormGent\App\Fields\PhoneNumber;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Fields\Field;

class PhoneNumber extends Field {
    use MethodResolver;

    protected function get_validation_rules( array $field ): array {
        return ['string', 'max:50'];
    }
}