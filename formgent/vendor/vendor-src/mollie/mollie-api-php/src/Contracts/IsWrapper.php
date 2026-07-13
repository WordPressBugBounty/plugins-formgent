<?php

namespace FormGent\Mollie\Api\Contracts;

use FormGent\Mollie\Api\Http\Response;
use FormGent\Mollie\Api\Resources\BaseCollection;
use FormGent\Mollie\Api\Resources\BaseResource;
use FormGent\Mollie\Api\Resources\LazyCollection;
interface IsWrapper extends ViableResponse
{
    /**
     * @param  Response|BaseResource|BaseCollection|LazyCollection  $resource
     */
    public static function fromResource($resource) : self;
}
