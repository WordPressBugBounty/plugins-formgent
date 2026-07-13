<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\Webhook;
use FormGent\Mollie\Api\Types\Method;
/**
 * @see https://docs.mollie.com/reference/get-webhook
 */
class GetWebhookRequest extends ResourceHydratableRequest implements SupportsTestmodeInQuery
{
    /**
     * Define the HTTP method.
     */
    protected static string $method = Method::GET;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = Webhook::class;
    private string $id;
    public function __construct(string $id)
    {
        $this->id = $id;
    }
    /**
     * The resource path.
     */
    public function resolveResourcePath() : string
    {
        return "webhooks/{$this->id}";
    }
}
