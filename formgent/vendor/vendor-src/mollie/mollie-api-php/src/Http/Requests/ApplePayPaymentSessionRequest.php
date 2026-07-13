<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\HasPayload;
use FormGent\Mollie\Api\Resources\AnyResource;
use FormGent\Mollie\Api\Traits\HasJsonPayload;
use FormGent\Mollie\Api\Types\Method;
class ApplePayPaymentSessionRequest extends ResourceHydratableRequest implements HasPayload
{
    use HasJsonPayload;
    protected static string $method = Method::POST;
    protected $hydratableResource = AnyResource::class;
    private string $domain;
    private string $validationUrl;
    private ?string $profileId;
    public function __construct(string $domain, string $validationUrl, ?string $profileId = null)
    {
        $this->domain = $domain;
        $this->validationUrl = $validationUrl;
        $this->profileId = $profileId;
    }
    public function defaultPayload() : array
    {
        return ['domain' => $this->domain, 'validationUrl' => $this->validationUrl, 'profileId' => $this->profileId];
    }
    public function resolveResourcePath() : string
    {
        return 'wallets/applepay/sessions';
    }
}
