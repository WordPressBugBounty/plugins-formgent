<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\Profile;
use FormGent\Mollie\Api\Types\Method;
/**
 * @see https://docs.mollie.com/reference/v2/profiles-api/get-profile
 */
class GetProfileRequest extends ResourceHydratableRequest implements SupportsTestmodeInQuery
{
    protected static string $method = Method::GET;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = Profile::class;
    private string $id;
    public function __construct(string $id)
    {
        $this->id = $id;
    }
    public function resolveResourcePath() : string
    {
        return "profiles/{$this->id}";
    }
}
