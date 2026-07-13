<?php

namespace FormGent\Mollie\Api\Traits;

use FormGent\Mollie\Api\Contracts\Repository;
use FormGent\Mollie\Api\Repositories\ArrayStore;
trait HasQuery
{
    protected Repository $queryStore;
    public function query() : Repository
    {
        return $this->queryStore ??= new ArrayStore($this->defaultQuery());
    }
    protected function defaultQuery() : array
    {
        return [];
    }
}
