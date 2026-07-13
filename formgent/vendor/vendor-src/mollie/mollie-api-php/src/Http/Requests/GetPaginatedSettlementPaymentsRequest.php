<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\IsIteratable;
use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\PaymentCollection;
use FormGent\Mollie\Api\Traits\IsIteratableRequest;
class GetPaginatedSettlementPaymentsRequest extends SortablePaginatedRequest implements IsIteratable, SupportsTestmodeInQuery
{
    use IsIteratableRequest;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = PaymentCollection::class;
    private string $settlementId;
    public function __construct(string $settlementId, ?string $from = null, ?int $limit = null, ?string $sort = null)
    {
        $this->settlementId = $settlementId;
        parent::__construct($from, $limit, $sort);
    }
    /**
     * Resolve the resource path.
     */
    public function resolveResourcePath() : string
    {
        return "settlements/{$this->settlementId}/payments";
    }
}
