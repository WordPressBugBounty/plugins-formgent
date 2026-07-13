<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Resources\Onboarding;
use FormGent\Mollie\Api\Types\Method;
class GetOnboardingStatusRequest extends ResourceHydratableRequest
{
    protected static string $method = Method::GET;
    protected $hydratableResource = Onboarding::class;
    public function resolveResourcePath() : string
    {
        return 'onboarding/me';
    }
}
