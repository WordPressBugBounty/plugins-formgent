<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\SupportsTestmodeInPayload;
use FormGent\Mollie\Api\Http\Request;
use FormGent\Mollie\Api\Traits\HasJsonPayload;
use FormGent\Mollie\Api\Types\Method;
/**
 * @see https://docs.mollie.com/reference/v2/customers-api/delete-customer
 */
class DeleteCustomerRequest extends Request implements SupportsTestmodeInPayload
{
    use HasJsonPayload;
    protected static string $method = Method::DELETE;
    protected string $id;
    public function __construct(string $id)
    {
        $this->id = $id;
    }
    public function resolveResourcePath() : string
    {
        return "customers/{$this->id}";
    }
}
