<?php

namespace FormGent\Mollie\Api\Http\Middleware;

use FormGent\Mollie\Api\Contracts\ResponseMiddleware;
use FormGent\Mollie\Api\Http\Response;
class ResetIdempotencyKey implements ResponseMiddleware
{
    /**
     * @param  Response|ViableResponse|mixed  $response
     */
    public function __invoke($response) : void
    {
        $response->getConnector()->resetIdempotencyKey();
    }
}
