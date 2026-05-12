<?php

namespace FormGent\App\Fields\Login;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Fields\Field;

class Login extends Field {
    public $has_children = true;

    use MethodResolver;
}
