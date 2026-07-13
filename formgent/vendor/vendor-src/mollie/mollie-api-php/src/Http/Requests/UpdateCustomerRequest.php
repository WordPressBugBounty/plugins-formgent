<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\HasPayload;
use FormGent\Mollie\Api\Resources\Customer;
use FormGent\Mollie\Api\Traits\HasJsonPayload;
use FormGent\Mollie\Api\Types\Method;
/**
 * @see https://docs.mollie.com/reference/v2/customers-api/update-customer
 */
class UpdateCustomerRequest extends ResourceHydratableRequest implements HasPayload
{
    use HasJsonPayload;
    protected static string $method = Method::PATCH;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = Customer::class;
    private string $id;
    private ?string $name;
    private ?string $email;
    private ?string $locale;
    private ?array $metadata;
    public function __construct(string $id, ?string $name = null, ?string $email = null, ?string $locale = null, ?array $metadata = null)
    {
        $this->id = $id;
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
        return "customers/{$this->id}";
    }
}
