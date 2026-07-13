<?php

namespace FormGent\Mollie\Api\Factories;

use FormGent\Mollie\Api\Http\Requests\CreateWebhookRequest;
class CreateWebhookRequestFactory extends RequestFactory
{
    public function create() : CreateWebhookRequest
    {
        return new CreateWebhookRequest($this->payload('url'), $this->payload('name'), $this->payload('eventTypes'));
    }
}
