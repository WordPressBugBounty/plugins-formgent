<?php

namespace FormGent\Mollie\Api\Http\Middleware;

use FormGent\Mollie\Api\Contracts\ResponseMiddleware;
use FormGent\Mollie\Api\Http\Requests\ResourceHydratableRequest;
use FormGent\Mollie\Api\Http\Response;
use FormGent\Mollie\Api\Resources\ResourceHydrator;
use FormGent\Mollie\Api\Resources\ResourceResolver;
class Hydrate implements ResponseMiddleware
{
    public function __invoke(Response $response)
    {
        $request = $response->getRequest();
        if (!$response->isEmpty() && $request instanceof ResourceHydratableRequest && $request->isHydratable()) {
            return (new ResourceResolver(new ResourceHydrator()))->resolve($request, $response);
        }
        return $response;
    }
}
