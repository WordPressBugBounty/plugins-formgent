<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\Capture;
use FormGent\Mollie\Api\Types\Method;
use FormGent\Mollie\Api\Types\PaymentIncludesQuery;
use FormGent\Mollie\Api\Utils\Arr;
/**
 * @see https://docs.mollie.com/reference/v2/captures-api/get-capture
 */
class GetPaymentCaptureRequest extends ResourceHydratableRequest implements SupportsTestmodeInQuery
{
    /**
     * Define the HTTP method.
     */
    protected static string $method = Method::GET;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = Capture::class;
    private string $paymentId;
    private string $captureId;
    private bool $embedPayment;
    public function __construct(string $paymentId, string $captureId, bool $embedPayment = \false)
    {
        $this->paymentId = $paymentId;
        $this->captureId = $captureId;
        $this->embedPayment = $embedPayment;
    }
    protected function defaultQuery() : array
    {
        return ['embed' => Arr::join($this->embedPayment ? [PaymentIncludesQuery::PAYMENT] : [])];
    }
    public function resolveResourcePath() : string
    {
        return "payments/{$this->paymentId}/captures/{$this->captureId}";
    }
}
