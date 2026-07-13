<?php

namespace FormGent\Mollie\Api\Contracts;

use FormGent\Mollie\Api\Http\PendingRequest;
interface RequestMiddleware
{
    /**
     * @return PendingRequest|void
     */
    public function __invoke(PendingRequest $pendingRequest);
}
