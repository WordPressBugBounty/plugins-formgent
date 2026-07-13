<?php

namespace FormGent\Mollie\Api\Traits;

use FormGent\Mollie\Api\Http\Middleware;
trait HasMiddleware
{
    protected Middleware $middleware;
    public function middleware() : Middleware
    {
        return $this->middleware ??= new Middleware();
    }
}
