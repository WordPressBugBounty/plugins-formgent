<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\IsIteratable;
use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\ChargebackCollection;
use FormGent\Mollie\Api\Traits\IsIteratableRequest;
use FormGent\Mollie\Api\Types\PaymentIncludesQuery;
class GetPaginatedPaymentChargebacksRequest extends PaginatedRequest implements IsIteratable, SupportsTestmodeInQuery
{
    use IsIteratableRequest;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = ChargebackCollection::class;
    private string $paymentId;
    public function __construct(string $paymentId, ?string $from = null, ?int $limit = null, bool $includePayment = \false)
    {
        $this->paymentId = $paymentId;
        parent::__construct($from, $limit);
        $this->query()->add('include', $includePayment ? PaymentIncludesQuery::PAYMENT : null);
    }
    public function resolveResourcePath() : string
    {
        return "payments/{$this->paymentId}/chargebacks";
    }
}
