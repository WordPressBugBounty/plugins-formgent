<?php

namespace FormGent\Mollie\Api\Factories;

use FormGent\Mollie\Api\Http\Data\SortablePaginatedQuery;
class SortablePaginatedQueryFactory extends RequestFactory
{
    public function create() : SortablePaginatedQuery
    {
        return new SortablePaginatedQuery($this->query('from'), $this->query('limit'), $this->query('sort'));
    }
}
