<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\Subscription;
use FormGent\Mollie\Api\Types\Method;
/**
 * @see https://docs.mollie.com/reference/v2/subscriptions-api/get-subscription
 */
class GetSubscriptionRequest extends ResourceHydratableRequest implements SupportsTestmodeInQuery
{
    /**
     * Define the HTTP method.
     */
    protected static string $method = Method::GET;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = Subscription::class;
    private string $customerId;
    private string $id;
    public function __construct(string $customerId, string $id)
    {
        $this->customerId = $customerId;
        $this->id = $id;
    }
    public function resolveResourcePath() : string
    {
        return "customers/{$this->customerId}/subscriptions/{$this->id}";
    }
}
