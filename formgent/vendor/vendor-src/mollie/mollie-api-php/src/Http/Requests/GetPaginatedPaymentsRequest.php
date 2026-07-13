<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\IsIteratable;
use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\PaymentCollection;
use FormGent\Mollie\Api\Traits\IsIteratableRequest;
class GetPaginatedPaymentsRequest extends SortablePaginatedRequest implements IsIteratable, SupportsTestmodeInQuery
{
    use IsIteratableRequest;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = PaymentCollection::class;
    /**
     * Resolve the resource path.
     */
    public function resolveResourcePath() : string
    {
        return 'payments';
    }
}
