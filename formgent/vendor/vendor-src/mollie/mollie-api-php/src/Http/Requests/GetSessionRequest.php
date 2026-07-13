<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Resources\Session;
use FormGent\Mollie\Api\Types\Method;
class GetSessionRequest extends ResourceHydratableRequest
{
    protected static string $method = Method::GET;
    protected $hydratableResource = Session::class;
    private string $id;
    public function __construct(string $id)
    {
        $this->id = $id;
    }
    public function resolveResourcePath() : string
    {
        return 'sessions/' . $this->id;
    }
}
