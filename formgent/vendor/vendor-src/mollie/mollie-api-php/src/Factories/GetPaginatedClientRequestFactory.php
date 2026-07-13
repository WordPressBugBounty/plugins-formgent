<?php

namespace FormGent\Mollie\Api\Factories;

use FormGent\Mollie\Api\Http\Requests\GetPaginatedClientRequest;
use FormGent\Mollie\Api\Types\ClientQuery;
class GetPaginatedClientRequestFactory extends RequestFactory
{
    public function create() : GetPaginatedClientRequest
    {
        $embedOrganization = $this->queryIncludes('embed', ClientQuery::EMBED_ORGANIZATION);
        $embedOnboarding = $this->queryIncludes('embed', ClientQuery::EMBED_ONBOARDING);
        return new GetPaginatedClientRequest($this->query('from'), $this->query('limit'), $this->query('embedOrganization', $embedOrganization), $this->query('embedOnboarding', $embedOnboarding));
    }
}
