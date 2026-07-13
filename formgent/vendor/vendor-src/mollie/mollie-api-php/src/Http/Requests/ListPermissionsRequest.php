<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Resources\PermissionCollection;
use FormGent\Mollie\Api\Types\Method;
/**
 * @see https://docs.mollie.com/reference/v2/permissions-api/list-permissions
 */
class ListPermissionsRequest extends ResourceHydratableRequest
{
    protected static string $method = Method::GET;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = PermissionCollection::class;
    public function resolveResourcePath() : string
    {
        return 'permissions';
    }
}
