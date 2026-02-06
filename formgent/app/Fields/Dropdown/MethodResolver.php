<?php

namespace FormGent\App\Fields\Dropdown;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Fields\SingleChoice\MethodResolver as SingleChoiceMethodResolver;

trait MethodResolver {

    use SingleChoiceMethodResolver;

    public static function get_key(): string {
        return 'dropdown';
    }
}
