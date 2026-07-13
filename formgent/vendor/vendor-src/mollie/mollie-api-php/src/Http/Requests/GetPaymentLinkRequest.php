<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\PaymentLink;
use FormGent\Mollie\Api\Types\Method;
/**
 * @see https://docs.mollie.com/reference/v2/payment-links-api/get-payment-link
 */
class GetPaymentLinkRequest extends ResourceHydratableRequest implements SupportsTestmodeInQuery
{
    /**
     * Define the HTTP method.
     */
    protected static string $method = Method::GET;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = PaymentLink::class;
    private string $id;
    public function __construct(string $id)
    {
        $this->id = $id;
    }
    /**
     * Resolve the resource path.
     */
    public function resolveResourcePath() : string
    {
        return "payment-links/{$this->id}";
    }
}
