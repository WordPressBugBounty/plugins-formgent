<?php

namespace FormGent\Mollie\Api\Factories;

use FormGent\Mollie\Api\Http\Requests\GetPaymentCaptureRequest;
use FormGent\Mollie\Api\Types\PaymentIncludesQuery;
class GetPaymentCaptureRequestFactory extends RequestFactory
{
    private string $paymentId;
    private string $captureId;
    public function __construct(string $paymentId, string $captureId)
    {
        $this->paymentId = $paymentId;
        $this->captureId = $captureId;
    }
    public function create() : GetPaymentCaptureRequest
    {
        $embedPayment = $this->queryIncludes('embed', PaymentIncludesQuery::PAYMENT);
        return new GetPaymentCaptureRequest($this->paymentId, $this->captureId, $this->query('includePayment', $embedPayment));
    }
}
