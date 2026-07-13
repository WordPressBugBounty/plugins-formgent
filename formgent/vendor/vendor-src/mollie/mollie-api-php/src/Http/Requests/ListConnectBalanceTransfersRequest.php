<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\IsIteratable;
use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\ConnectBalanceTransferCollection;
use FormGent\Mollie\Api\Traits\IsIteratableRequest;
class ListConnectBalanceTransfersRequest extends SortablePaginatedRequest implements IsIteratable, SupportsTestmodeInQuery
{
    use IsIteratableRequest;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = ConnectBalanceTransferCollection::class;
    /**
     * The resource path.
     */
    public function resolveResourcePath() : string
    {
        return 'connect/balance-transfers';
    }
}
