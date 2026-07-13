<?php

namespace FormGent\Mollie\Api\Traits;

use FormGent\Mollie\Api\Contracts\Repository;
use FormGent\Mollie\Api\Repositories\ArrayStore;
trait HasHeaders
{
    protected Repository $headers;
    public function headers() : Repository
    {
        return $this->headers ??= new ArrayStore($this->defaultHeaders());
    }
    protected function defaultHeaders() : array
    {
        return [];
    }
}
