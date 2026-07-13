<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\IsIteratable;
use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\BalanceCollection;
use FormGent\Mollie\Api\Traits\IsIteratableRequest;
class GetPaginatedBalanceRequest extends SortablePaginatedRequest implements IsIteratable, SupportsTestmodeInQuery
{
    use IsIteratableRequest;
    protected $hydratableResource = BalanceCollection::class;
    public function resolveResourcePath() : string
    {
        return 'balances';
    }
}
