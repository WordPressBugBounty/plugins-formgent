<?php

namespace FormGent\App\Fields\Address;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Fields\Field;

class Address extends Field {
    public $has_children = true;

    use MethodResolver;
}
