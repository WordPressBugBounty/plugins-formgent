<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\Organization;
use FormGent\Mollie\Api\Types\Method;
/**
 * @see https://docs.mollie.com/reference/v2/organizations-api/get-organization
 */
class GetOrganizationRequest extends ResourceHydratableRequest implements SupportsTestmodeInQuery
{
    protected static string $method = Method::GET;
    protected $hydratableResource = Organization::class;
    private string $id;
    public function __construct(string $id)
    {
        $this->id = $id;
    }
    public function resolveResourcePath() : string
    {
        return "organizations/{$this->id}";
    }
}
