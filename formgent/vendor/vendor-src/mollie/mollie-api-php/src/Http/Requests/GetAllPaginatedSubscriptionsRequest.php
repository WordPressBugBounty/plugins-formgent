<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\IsIteratable;
use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\SubscriptionCollection;
use FormGent\Mollie\Api\Traits\IsIteratableRequest;
use FormGent\Mollie\Api\Types\Method;
class GetAllPaginatedSubscriptionsRequest extends ResourceHydratableRequest implements IsIteratable, SupportsTestmodeInQuery
{
    use IsIteratableRequest;
    protected static string $method = Method::GET;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = SubscriptionCollection::class;
    private ?string $profileId;
    private ?string $from;
    private ?string $limit;
    public function __construct(?string $from = null, ?string $limit = null, ?string $profileId = null)
    {
        $this->from = $from;
        $this->limit = $limit;
        $this->profileId = $profileId;
    }
    public function defaultQuery() : array
    {
        return ['from' => $this->from, 'limit' => $this->limit, 'profileId' => $this->profileId];
    }
    /**
     * The resource path.
     */
    public function resolveResourcePath() : string
    {
        return 'subscriptions';
    }
}
