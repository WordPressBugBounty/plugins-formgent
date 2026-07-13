<?php

namespace FormGent\Mollie\Api\EndpointCollection;

use FormGent\Mollie\Api\Http\Requests\GetOnboardingStatusRequest;
use FormGent\Mollie\Api\Resources\Onboarding;
class OnboardingEndpointCollection extends EndpointCollection
{
    public function status() : Onboarding
    {
        return $this->send(new GetOnboardingStatusRequest());
    }
}
