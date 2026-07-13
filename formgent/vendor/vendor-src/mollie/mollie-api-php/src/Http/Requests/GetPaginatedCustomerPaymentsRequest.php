<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\IsIteratable;
use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\PaymentCollection;
use FormGent\Mollie\Api\Traits\IsIteratableRequest;
class GetPaginatedCustomerPaymentsRequest extends SortablePaginatedRequest implements IsIteratable, SupportsTestmodeInQuery
{
    use IsIteratableRequest;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = PaymentCollection::class;
    private string $customerId;
    public function __construct(string $customerId, ?string $from = null, ?int $limit = null, ?string $sort = null, ?string $profileId = null)
    {
        $this->customerId = $customerId;
        parent::__construct($from, $limit, $sort);
        $this->query()->add('profileId', $profileId);
    }
    public function resolveResourcePath() : string
    {
        return "customers/{$this->customerId}/payments";
    }
}
