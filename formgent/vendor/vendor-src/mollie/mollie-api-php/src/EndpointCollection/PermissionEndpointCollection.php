<?php

namespace FormGent\Mollie\Api\EndpointCollection;

use FormGent\Mollie\Api\Exceptions\RequestException;
use FormGent\Mollie\Api\Http\Requests\GetPermissionRequest;
use FormGent\Mollie\Api\Http\Requests\ListPermissionsRequest;
use FormGent\Mollie\Api\Resources\Permission;
use FormGent\Mollie\Api\Resources\PermissionCollection;
use FormGent\Mollie\Api\Utils\Utility;
class PermissionEndpointCollection extends EndpointCollection
{
    /**
     * Retrieve a single Permission from Mollie.
     *
     * Will throw an ApiException if the permission id is invalid.
     *
     * @param  bool|array  $testmode
     *
     * @throws RequestException
     */
    public function get(string $permissionId, $testmode = \false) : Permission
    {
        $testmode = Utility::extractBool($testmode, 'testmode', \false);
        /** @var Permission */
        return $this->send((new GetPermissionRequest($permissionId))->test($testmode));
    }
    /**
     * Retrieve all permissions from Mollie.
     *
     * @throws RequestException
     */
    public function list() : PermissionCollection
    {
        /** @var PermissionCollection */
        return $this->send(new ListPermissionsRequest());
    }
}
