<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\IsIteratable;
use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\PaymentLinkCollection;
use FormGent\Mollie\Api\Traits\IsIteratableRequest;
class GetPaginatedPaymentLinksRequest extends PaginatedRequest implements IsIteratable, SupportsTestmodeInQuery
{
    use IsIteratableRequest;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = PaymentLinkCollection::class;
    public function resolveResourcePath() : string
    {
        return 'payment-links';
    }
}
