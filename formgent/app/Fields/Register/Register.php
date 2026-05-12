<?php

namespace FormGent\App\Fields\Register;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Fields\Field;

class Register extends Field {
    public $has_children = true;

    use MethodResolver;
}

