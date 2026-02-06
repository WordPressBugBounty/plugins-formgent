<?php

namespace FormGent\App\Fields\PhoneNumber;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Summary\Pagination;

trait MethodResolver {

    use Pagination;

    public static function get_key(): string {
        return 'phone-number';
    }
}
