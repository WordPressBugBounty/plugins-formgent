<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\IsIteratable;
use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Traits\IsIteratableRequest;
class DynamicPaginatedRequest extends DynamicGetRequest implements IsIteratable, SupportsTestmodeInQuery
{
    use IsIteratableRequest;
}
