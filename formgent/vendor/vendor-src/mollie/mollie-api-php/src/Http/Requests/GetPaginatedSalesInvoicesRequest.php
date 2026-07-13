<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\IsIteratable;
use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\SalesInvoiceCollection;
use FormGent\Mollie\Api\Traits\IsIteratableRequest;
class GetPaginatedSalesInvoicesRequest extends PaginatedRequest implements IsIteratable, SupportsTestmodeInQuery
{
    use IsIteratableRequest;
    protected $hydratableResource = SalesInvoiceCollection::class;
    public function resolveResourcePath() : string
    {
        return 'sales-invoices';
    }
}
