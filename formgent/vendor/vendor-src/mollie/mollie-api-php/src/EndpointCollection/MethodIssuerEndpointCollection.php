<?php

namespace FormGent\Mollie\Api\EndpointCollection;

use FormGent\Mollie\Api\Exceptions\RequestException;
use FormGent\Mollie\Api\Http\Requests\DisableMethodIssuerRequest;
use FormGent\Mollie\Api\Http\Requests\EnableMethodIssuerRequest;
use FormGent\Mollie\Api\Resources\Issuer;
class MethodIssuerEndpointCollection extends EndpointCollection
{
    /**
     * Enable an issuer for a specific payment method.
     *
     * @throws RequestException
     */
    public function enable(string $profileId, string $methodId, string $issuerId, ?string $contractId = null) : Issuer
    {
        return $this->send(new EnableMethodIssuerRequest($profileId, $methodId, $issuerId, $contractId));
    }
    /**
     * Disable an issuer for a specific payment method.
     *
     * @throws RequestException
     */
    public function disable(string $profileId, string $methodId, string $issuerId) : void
    {
        $this->send(new DisableMethodIssuerRequest($profileId, $methodId, $issuerId));
    }
}
