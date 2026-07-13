<?php

namespace FormGent\Mollie\Api\EndpointCollection;

use FormGent\Mollie\Api\Http\Requests\GetOrganizationPartnerStatusRequest;
use FormGent\Mollie\Api\Resources\Partner;
/**
 * @deprecated Use OrganizationEndpointCollection::partnerStatus() instead
 */
class OrganizationPartnerEndpointCollection extends EndpointCollection
{
    public function status() : Partner
    {
        return $this->send(new GetOrganizationPartnerStatusRequest());
    }
}
