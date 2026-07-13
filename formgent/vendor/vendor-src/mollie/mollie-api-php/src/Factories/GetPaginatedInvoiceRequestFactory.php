<?php

namespace FormGent\Mollie\Api\Factories;

use FormGent\Mollie\Api\Http\Requests\GetPaginatedInvoiceRequest;
class GetPaginatedInvoiceRequestFactory extends RequestFactory
{
    public function create() : GetPaginatedInvoiceRequest
    {
        return new GetPaginatedInvoiceRequest($this->query('from'), $this->query('limit'), $this->query('reference'), $this->query('year'));
    }
}
