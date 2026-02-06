<?php

namespace FormGent\App\Fields\Name;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Fields\Field;

class Name extends Field {
    public $has_children = true;

    use MethodResolver;
}
