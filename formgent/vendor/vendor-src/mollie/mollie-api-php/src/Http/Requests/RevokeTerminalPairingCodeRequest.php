<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Resources\TerminalPairingCode;
use FormGent\Mollie\Api\Types\Method;
/**
 * @see https://docs.mollie.com/reference/terminals-revoke-pairing-code
 */
class RevokeTerminalPairingCodeRequest extends ResourceHydratableRequest
{
    protected static string $method = Method::DELETE;
    protected $hydratableResource = TerminalPairingCode::class;
    private string $id;
    public function __construct(string $id)
    {
        $this->id = $id;
    }
    public function resolveResourcePath() : string
    {
        return "terminals/pairing-codes/{$this->id}";
    }
}
