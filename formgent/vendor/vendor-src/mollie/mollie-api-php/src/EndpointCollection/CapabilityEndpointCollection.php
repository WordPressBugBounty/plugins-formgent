<?php

namespace FormGent\Mollie\Api\EndpointCollection;

use FormGent\Mollie\Api\Exceptions\RequestException;
use FormGent\Mollie\Api\Http\Requests\GetCapabilityRequest;
use FormGent\Mollie\Api\Http\Requests\ListCapabilitiesRequest;
use FormGent\Mollie\Api\Resources\Capability;
use FormGent\Mollie\Api\Resources\CapabilityCollection;
class CapabilityEndpointCollection extends EndpointCollection
{
    /**
     * Retrieve a single Permission from Mollie.
     *
     * Will throw an ApiException if the permission id is invalid.
     *
     * @throws RequestException
     */
    public function get(string $name) : Capability
    {
        /** @var Capability */
        return $this->send(new GetCapabilityRequest($name));
    }
    /**
     * Retrieve all capabilities from Mollie.
     *
     * @throws RequestException
     */
    public function list() : CapabilityCollection
    {
        /** @var CapabilityCollection */
        return $this->send(new ListCapabilitiesRequest());
    }
}
