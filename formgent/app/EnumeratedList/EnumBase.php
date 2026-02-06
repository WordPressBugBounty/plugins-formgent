<?php

namespace FormGent\App\EnumeratedList;

defined( 'ABSPATH' ) || exit;

use ReflectionClass;

abstract class EnumBase {
    public static function values(): string {
        return implode( ',', array_values( ( new ReflectionClass( get_called_class() ) )->getConstants() ) );
    }
}