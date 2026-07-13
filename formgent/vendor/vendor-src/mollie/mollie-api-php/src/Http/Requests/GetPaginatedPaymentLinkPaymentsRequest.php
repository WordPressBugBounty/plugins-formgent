<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\IsIteratable;
use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\PaymentCollection;
use FormGent\Mollie\Api\Traits\IsIteratableRequest;
class GetPaginatedPaymentLinkPaymentsRequest extends SortablePaginatedRequest implements IsIteratable, SupportsTestmodeInQuery
{
    use IsIteratableRequest;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = PaymentCollection::class;
    private string $paymentLinkId;
    public function __construct(string $paymentLinkId, ?string $from = null, ?int $limit = null, ?string $sort = null)
    {
        $this->paymentLinkId = $paymentLinkId;
        parent::__construct($from, $limit, $sort);
    }
    /**
     * Resolve the resource path.
     */
    public function resolveResourcePath() : string
    {
        return "payment-links/{$this->paymentLinkId}/payments";
    }
}
