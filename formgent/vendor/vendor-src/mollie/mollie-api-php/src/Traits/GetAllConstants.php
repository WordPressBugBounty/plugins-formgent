<?php

namespace FormGent\Mollie\Api\Traits;

trait GetAllConstants
{
    public static function all() : array
    {
        $reflection = new \ReflectionClass(self::class);
        return \array_values($reflection->getConstants());
    }
}
