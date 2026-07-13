<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\SupportsTestmodeInPayload;
use FormGent\Mollie\Api\Resources\Payment;
use FormGent\Mollie\Api\Traits\HasJsonPayload;
use FormGent\Mollie\Api\Types\Method;
/**
 * @see https://docs.mollie.com/reference/v2/payments-api/cancel-payment
 */
class CancelPaymentRequest extends ResourceHydratableRequest implements SupportsTestmodeInPayload
{
    use HasJsonPayload;
    protected static string $method = Method::DELETE;
    protected $hydratableResource = Payment::class;
    protected string $id;
    public function __construct(string $id)
    {
        $this->id = $id;
    }
    public function resolveResourcePath() : string
    {
        return "payments/{$this->id}";
    }
}
