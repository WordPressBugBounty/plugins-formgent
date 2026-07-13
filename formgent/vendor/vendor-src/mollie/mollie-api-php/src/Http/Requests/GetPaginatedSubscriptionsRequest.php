<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\IsIteratable;
use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\SubscriptionCollection;
use FormGent\Mollie\Api\Traits\IsIteratableRequest;
class GetPaginatedSubscriptionsRequest extends PaginatedRequest implements IsIteratable, SupportsTestmodeInQuery
{
    use IsIteratableRequest;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = SubscriptionCollection::class;
    private string $customerId;
    public function __construct(string $customerId, ?string $from = null, ?int $limit = null)
    {
        $this->customerId = $customerId;
        parent::__construct($from, $limit);
    }
    /**
     * The resource path.
     */
    public function resolveResourcePath() : string
    {
        return "customers/{$this->customerId}/subscriptions";
    }
}
