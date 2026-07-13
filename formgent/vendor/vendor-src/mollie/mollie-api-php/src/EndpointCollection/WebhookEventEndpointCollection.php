<?php

namespace FormGent\Mollie\Api\EndpointCollection;

use FormGent\Mollie\Api\Exceptions\RequestException;
use FormGent\Mollie\Api\Http\Requests\GetWebhookEventRequest;
use FormGent\Mollie\Api\Resources\WebhookEvent;
class WebhookEventEndpointCollection extends EndpointCollection
{
    /**
     * Retrieve a webhook event from Mollie.
     *
     * Will throw an ApiException if the webhook event id is invalid or the resource cannot be found.
     *
     * @throws RequestException
     */
    public function get(string $id) : WebhookEvent
    {
        /** @var WebhookEvent */
        return $this->send(new GetWebhookEventRequest($id));
    }
}
