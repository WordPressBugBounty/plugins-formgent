<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\IsIteratable;
use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\PayoutCollection;
use FormGent\Mollie\Api\Traits\IsIteratableRequest;
class GetPaginatedPayoutsRequest extends SortablePaginatedRequest implements IsIteratable, SupportsTestmodeInQuery
{
    use IsIteratableRequest;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = PayoutCollection::class;
    public function __construct(?string $from = null, ?int $limit = null, ?string $sort = null, ?string $balanceId = null)
    {
        parent::__construct($from, $limit, $sort);
        $this->query()->add('balanceId', $balanceId);
    }
    /**
     * The resource path.
     */
    public function resolveResourcePath() : string
    {
        return 'payouts';
    }
}
