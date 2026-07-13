<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\HasPayload;
use FormGent\Mollie\Api\Contracts\SupportsTestmodeInPayload;
use FormGent\Mollie\Api\Resources\Customer;
use FormGent\Mollie\Api\Traits\HasJsonPayload;
use FormGent\Mollie\Api\Types\Method;
/**
 * @see https://docs.mollie.com/reference/v2/customers-api/create-customer
 */
class CreateCustomerRequest extends ResourceHydratableRequest implements HasPayload, SupportsTestmodeInPayload
{
    use HasJsonPayload;
    protected static string $method = Method::POST;
    protected $hydratableResource = Customer::class;
    private ?string $name;
    private ?string $email;
    private ?string $locale;
    private ?array $metadata;
    public function __construct(?string $name = null, ?string $email = null, ?string $locale = null, ?array $metadata = null)
    {
        $this->name = $name;
        $this->email = $email;
        $this->locale = $locale;
        $this->metadata = $metadata;
    }
    protected function defaultPayload() : array
    {
        return ['name' => $this->name, 'email' => $this->email, 'locale' => $this->locale, 'metadata' => $this->metadata];
    }
    public function resolveResourcePath() : string
    {
        return 'customers';
    }
}
