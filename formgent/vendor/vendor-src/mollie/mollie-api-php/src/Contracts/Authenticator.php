<?php

namespace FormGent\Mollie\Api\Contracts;

use FormGent\Mollie\Api\Http\PendingRequest;
interface Authenticator
{
    public function authenticate(PendingRequest $pendingRequest) : void;
}
