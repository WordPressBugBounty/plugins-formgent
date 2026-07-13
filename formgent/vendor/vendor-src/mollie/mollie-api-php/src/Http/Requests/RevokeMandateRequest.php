<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\SupportsTestmodeInPayload;
use FormGent\Mollie\Api\Http\Request;
use FormGent\Mollie\Api\Traits\HasJsonPayload;
use FormGent\Mollie\Api\Types\Method;
/**
 * @see https://docs.mollie.com/reference/v2/mandates-api/revoke-mandate
 */
class RevokeMandateRequest extends Request implements SupportsTestmodeInPayload
{
    use HasJsonPayload;
    /**
     * Define the HTTP method.
     */
    protected static string $method = Method::DELETE;
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
