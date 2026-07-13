<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\HasPayload;
use FormGent\Mollie\Api\Contracts\SupportsTestmodeInPayload;
use FormGent\Mollie\Api\Http\Data\Money;
use FormGent\Mollie\Api\Resources\Route;
use FormGent\Mollie\Api\Traits\HasJsonPayload;
use FormGent\Mollie\Api\Types\Method;
class CreateDelayedPaymentRouteRequest extends ResourceHydratableRequest implements HasPayload, SupportsTestmodeInPayload
{
    use HasJsonPayload;
    /**
     * The HTTP method.
     */
    protected static string $method = Method::POST;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = Route::class;
    private string $paymentId;
    private Money $amount;
    private array $destination;
    public function __construct(string $paymentId, Money $amount, array $destination)
    {
        $this->paymentId = $paymentId;
        $this->amount = $amount;
        $this->destination = $destination;
    }
    protected function defaultPayload() : array
    {
        return ['amount' => $this->amount, 'destination' => $this->destination];
    }
    public function resolveResourcePath() : string
    {
        return "payments/{$this->paymentId}/routes";
    }
}
