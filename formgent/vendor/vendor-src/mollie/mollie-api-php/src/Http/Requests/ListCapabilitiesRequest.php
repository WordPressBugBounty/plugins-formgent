<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Resources\CapabilityCollection;
use FormGent\Mollie\Api\Types\Method;
/**
 * @see https://docs.mollie.com/reference/list-capabilities
 */
class ListCapabilitiesRequest extends ResourceHydratableRequest
{
    protected static string $method = Method::GET;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = CapabilityCollection::class;
    public function resolveResourcePath() : string
    {
        return 'capabilities';
    }
}
