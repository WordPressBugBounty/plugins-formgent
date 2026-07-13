<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\Refund;
use FormGent\Mollie\Api\Types\Method;
use FormGent\Mollie\Api\Types\PaymentIncludesQuery;
/**
 * @see https://docs.mollie.com/reference/v2/refunds-api/get-refund
 */
class GetPaymentRefundRequest extends ResourceHydratableRequest implements SupportsTestmodeInQuery
{
    /**
     * Define the HTTP method.
     */
    protected static string $method = Method::GET;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = Refund::class;
    private string $paymentId;
    private string $refundId;
    private bool $includePayment;
    public function __construct(string $paymentId, string $refundId, bool $includePayment = \false)
    {
        $this->paymentId = $paymentId;
        $this->refundId = $refundId;
        $this->includePayment = $includePayment;
    }
    protected function defaultQuery() : array
    {
        return ['include' => $this->includePayment ? PaymentIncludesQuery::PAYMENT : null];
    }
    /**
     * Resolve the resource path.
     */
    public function resolveResourcePath() : string
    {
        return "payments/{$this->paymentId}/refunds/{$this->refundId}";
    }
}
