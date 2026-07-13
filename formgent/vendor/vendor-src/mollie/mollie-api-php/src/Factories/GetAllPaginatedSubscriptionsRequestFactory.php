<?php

namespace FormGent\Mollie\Api\Factories;

use FormGent\Mollie\Api\Http\Requests\GetAllPaginatedSubscriptionsRequest;
class GetAllPaginatedSubscriptionsRequestFactory extends RequestFactory
{
    public function create() : GetAllPaginatedSubscriptionsRequest
    {
        return new GetAllPaginatedSubscriptionsRequest($this->query('limit'), $this->query('from'), $this->query('profileId'));
    }
}
