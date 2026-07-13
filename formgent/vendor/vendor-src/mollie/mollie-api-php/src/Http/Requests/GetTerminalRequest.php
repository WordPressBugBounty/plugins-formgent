<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\Terminal;
use FormGent\Mollie\Api\Types\Method;
/**
 * @see https://docs.mollie.com/reference/v2/terminals-api/get-terminal
 */
class GetTerminalRequest extends ResourceHydratableRequest implements SupportsTestmodeInQuery
{
    protected static string $method = Method::GET;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = Terminal::class;
    private string $id;
    public function __construct(string $id)
    {
        $this->id = $id;
    }
    /**
     * The resource path.
     */
    public function resolveResourcePath() : string
    {
        return "terminals/{$this->id}";
    }
}
