<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\Mandate;
use FormGent\Mollie\Api\Types\Method;
/**
 * @see https://docs.mollie.com/reference/v2/mandates-api/get-mandate
 */
class GetMandateRequest extends ResourceHydratableRequest implements SupportsTestmodeInQuery
{
    /**
     * Define the HTTP method.
     */
    protected static string $method = Method::GET;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = Mandate::class;
    private string $customerId;
    private string $mandateId;
    public function __construct(string $customerId, string $mandateId)
    {
        $this->customerId = $customerId;
        $this->mandateId = $mandateId;
    }
    public function resolveResourcePath() : string
    {
        return "customers/{$this->customerId}/mandates/{$this->mandateId}";
    }
}
