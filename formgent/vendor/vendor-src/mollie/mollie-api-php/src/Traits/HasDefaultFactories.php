<?php

namespace FormGent\Mollie\Api\Traits;

use FormGent\Mollie\Api\Utils\Factories;
use FormGent\Nyholm\Psr7\Factory\Psr17Factory;
trait HasDefaultFactories
{
    private static ?Factories $factories = null;
    public function factories() : Factories
    {
        if (self::$factories) {
            return self::$factories;
        }
        $httpFactory = new Psr17Factory();
        return self::$factories = new Factories($httpFactory, $httpFactory, $httpFactory, $httpFactory);
    }
}
