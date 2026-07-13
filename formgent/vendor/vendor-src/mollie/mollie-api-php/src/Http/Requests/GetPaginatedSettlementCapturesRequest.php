<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\IsIteratable;
use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\CaptureCollection;
use FormGent\Mollie\Api\Traits\IsIteratableRequest;
use FormGent\Mollie\Api\Types\PaymentIncludesQuery;
use FormGent\Mollie\Api\Utils\Arr;
class GetPaginatedSettlementCapturesRequest extends PaginatedRequest implements IsIteratable, SupportsTestmodeInQuery
{
    use IsIteratableRequest;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = CaptureCollection::class;
    private string $settlementId;
    public function __construct(string $settlementId, ?string $from = null, ?int $limit = null, bool $includePayment = \false)
    {
        $this->settlementId = $settlementId;
        parent::__construct($from, $limit);
        $this->query()->add('embed', Arr::join($includePayment ? [PaymentIncludesQuery::PAYMENT] : []));
    }
    /**
     * Resolve the resource path.
     */
    public function resolveResourcePath() : string
    {
        return "settlements/{$this->settlementId}/captures";
    }
}
