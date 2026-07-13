<?php

namespace FormGent\Mollie\Api\Factories;

use FormGent\Mollie\Api\Http\Requests\GetPaginatedChargebacksRequest;
use FormGent\Mollie\Api\Types\PaymentIncludesQuery;
class GetPaginatedChargebacksRequestFactory extends RequestFactory
{
    public function create() : GetPaginatedChargebacksRequest
    {
        $includePayment = $this->queryIncludes('include', PaymentIncludesQuery::PAYMENT);
        return new GetPaginatedChargebacksRequest($this->query('from'), $this->query('limit'), $this->query('includePayment', $includePayment), $this->query('profileId'));
    }
}
