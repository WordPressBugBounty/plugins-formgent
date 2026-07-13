<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Http\Request;
use FormGent\Mollie\Api\Types\Method;
/**
 * @see https://docs.mollie.com/reference/release-authorization
 */
class ReleasePaymentAuthorizationRequest extends Request
{
    protected static string $method = Method::POST;
    private string $paymentId;
    public function __construct(string $paymentId)
    {
        $this->paymentId = $paymentId;
    }
    public function resolveResourcePath() : string
    {
        return 'payments/' . $this->paymentId . '/release-authorization';
    }
}
