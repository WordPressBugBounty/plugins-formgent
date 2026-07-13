<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\IsIteratable;
use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\CustomerCollection;
use FormGent\Mollie\Api\Traits\IsIteratableRequest;
class GetPaginatedCustomerRequest extends PaginatedRequest implements IsIteratable, SupportsTestmodeInQuery
{
    use IsIteratableRequest;
    protected $hydratableResource = CustomerCollection::class;
    public function resolveResourcePath() : string
    {
        return 'customers';
    }
}
