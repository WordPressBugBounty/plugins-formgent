<?php

namespace FormGent\Mollie\Api\Traits;

use FormGent\Mollie\Api\Repositories\JsonPayloadRepository;
trait HasJsonPayload
{
    public ?JsonPayloadRepository $body = null;
    public function payload() : JsonPayloadRepository
    {
        return $this->body ??= new JsonPayloadRepository($this->defaultPayload());
    }
    protected function defaultPayload() : array
    {
        return [];
    }
}
