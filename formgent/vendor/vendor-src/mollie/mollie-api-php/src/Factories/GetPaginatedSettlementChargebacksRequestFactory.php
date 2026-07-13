<?php

namespace FormGent\Mollie\Api\Factories;

use FormGent\Mollie\Api\Http\Requests\GetPaginatedSettlementChargebacksRequest;
use FormGent\Mollie\Api\Types\PaymentIncludesQuery;
class GetPaginatedSettlementChargebacksRequestFactory extends RequestFactory
{
    private string $settlementId;
    public function __construct(string $settlementId)
    {
        $this->settlementId = $settlementId;
    }
    public function create() : GetPaginatedSettlementChargebacksRequest
    {
        $includePayment = $this->queryIncludes('include', PaymentIncludesQuery::PAYMENT);
        return new GetPaginatedSettlementChargebacksRequest($this->settlementId, $this->query('from'), $this->query('limit'), $this->query('includePayment', $includePayment), $this->query('profileId'));
    }
}
