<?php

namespace FormGent\Mollie\Api\Http;

use LogicException;
use FormGent\Mollie\Api\Traits\HandlesDebugging;
use FormGent\Mollie\Api\Traits\HandlesTestmode;
use FormGent\Mollie\Api\Traits\HasMiddleware;
use FormGent\Mollie\Api\Traits\HasRequestProperties;
abstract class Request
{
    use HandlesDebugging;
    use HandlesTestmode;
    use HasMiddleware;
    use HasRequestProperties;
    /**
     * Define the HTTP method.
     */
    protected static string $method;
    /**
     * Get the method of the request.
     */
    public function getMethod() : string
    {
        if (!isset(static::$method)) {
            throw new LogicException('Your request is missing a HTTP method. You must add a method property like [protected Method $method = Method::GET]');
        }
        return static::$method;
    }
    /**
     * Resolve the resource path.
     */
    public abstract function resolveResourcePath() : string;
}
