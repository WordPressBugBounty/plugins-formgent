<?php

namespace FormGent\Mollie\Api\Http\Auth;

use FormGent\Mollie\Api\Contracts\Authenticator;
use FormGent\Mollie\Api\Http\PendingRequest;
class BearerTokenAuthenticator implements Authenticator
{
    protected string $token;
    public function __construct(string $token)
    {
        $this->token = \trim($token);
    }
    public function authenticate(PendingRequest $pendingRequest) : void
    {
        $pendingRequest->headers()->add('Authorization', "Bearer {$this->token}");
    }
}
