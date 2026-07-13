<?php

namespace FormGent\Mollie\Api\Http\PendingRequest;

use FormGent\Mollie\Api\Exceptions\MissingAuthenticationException;
use FormGent\Mollie\Api\Http\PendingRequest;
class AuthenticateRequest
{
    public function __invoke(PendingRequest $pendingRequest) : PendingRequest
    {
        $authenticator = $pendingRequest->getConnector()->getAuthenticator();
        if (!$authenticator) {
            throw new MissingAuthenticationException();
        }
        $authenticator->authenticate($pendingRequest);
        return $pendingRequest;
    }
}
