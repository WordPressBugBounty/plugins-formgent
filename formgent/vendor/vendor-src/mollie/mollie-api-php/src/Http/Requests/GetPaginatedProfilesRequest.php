<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\IsIteratable;
use FormGent\Mollie\Api\Resources\ProfileCollection;
use FormGent\Mollie\Api\Traits\IsIteratableRequest;
class GetPaginatedProfilesRequest extends PaginatedRequest implements IsIteratable
{
    use IsIteratableRequest;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = ProfileCollection::class;
    /**
     * Resolve the resource path.
     */
    public function resolveResourcePath() : string
    {
        return 'profiles';
    }
}
