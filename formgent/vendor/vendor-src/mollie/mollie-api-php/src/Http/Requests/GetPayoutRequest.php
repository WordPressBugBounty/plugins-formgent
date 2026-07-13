<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\Payout;
use FormGent\Mollie\Api\Types\Method;
class GetPayoutRequest extends ResourceHydratableRequest implements SupportsTestmodeInQuery
{
    /**
     * Define the HTTP method.
     */
    protected static string $method = Method::GET;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = Payout::class;
    private string $id;
    public function __construct(string $id)
    {
        $this->id = $id;
    }
    /**
     * The resource path.
     */
    public function resolveResourcePath() : string
    {
        return "payouts/{$this->id}";
    }
}
