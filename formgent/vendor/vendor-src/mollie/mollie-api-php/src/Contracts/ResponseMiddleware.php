<?php

namespace FormGent\Mollie\Api\Contracts;

use FormGent\Mollie\Api\Http\Response;
use FormGent\Mollie\Api\Http\ViableResponse;
interface ResponseMiddleware
{
    /**
     * @return Response|ViableResponse|mixed|void
     */
    public function __invoke(Response $response);
}
