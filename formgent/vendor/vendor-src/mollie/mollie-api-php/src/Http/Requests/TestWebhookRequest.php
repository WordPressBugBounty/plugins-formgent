<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\SupportsTestmodeInPayload;
use FormGent\Mollie\Api\Resources\AnyResource;
use FormGent\Mollie\Api\Traits\HasJsonPayload;
use FormGent\Mollie\Api\Types\Method;
/**
 * @see https://docs.mollie.com/reference/test-webhook
 */
class TestWebhookRequest extends ResourceHydratableRequest implements SupportsTestmodeInPayload
{
    use HasJsonPayload;
    /**
     * Define the HTTP method.
     */
    protected static string $method = Method::POST;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = AnyResource::class;
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
        return "webhooks/{$this->id}/ping";
    }
}
