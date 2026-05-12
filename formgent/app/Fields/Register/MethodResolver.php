<?php

namespace FormGent\App\Fields\Register;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Fields\ParentMethodResolver;
use FormGent\App\Summary\HasChild;

trait MethodResolver {
    use ParentMethodResolver;
    use HasChild;

    public static function get_key(): string {
        return 'register';
    }
}

