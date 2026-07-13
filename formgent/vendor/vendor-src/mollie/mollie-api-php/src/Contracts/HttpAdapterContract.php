<?php

namespace FormGent\Mollie\Api\Contracts;

use FormGent\Mollie\Api\Http\PendingRequest;
use FormGent\Mollie\Api\Http\Response;
use FormGent\Mollie\Api\Utils\Factories;
interface HttpAdapterContract
{
    public function factories() : Factories;
    /**
     * Send a request to the specified Mollie api url.
     *
     * @throws \Mollie\Api\Exceptions\ApiException
     */
    public function sendRequest(PendingRequest $pendingRequest) : Response;
    /**
     * The version number for the underlying http client, if available.
     *
     * @example Guzzle/6.3
     */
    public function version() : ?string;
}
