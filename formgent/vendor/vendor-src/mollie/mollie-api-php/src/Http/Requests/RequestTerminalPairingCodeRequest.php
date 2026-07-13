<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\HasPayload;
use FormGent\Mollie\Api\Resources\TerminalPairingCode;
use FormGent\Mollie\Api\Traits\HasJsonPayload;
use FormGent\Mollie\Api\Types\Method;
use FormGent\Mollie\Api\Types\TerminalPairingCodeQuery;
/**
 * @see https://docs.mollie.com/reference/terminals-request-pairing-code
 */
class RequestTerminalPairingCodeRequest extends ResourceHydratableRequest implements HasPayload
{
    use HasJsonPayload;
    protected static string $method = Method::POST;
    protected $hydratableResource = TerminalPairingCode::class;
    private string $profileId;
    private bool $includeQrCode;
    public function __construct(string $profileId, bool $includeQrCode = \false)
    {
        $this->profileId = $profileId;
        $this->includeQrCode = $includeQrCode;
    }
    protected function defaultQuery() : array
    {
        return ['include' => $this->includeQrCode ? TerminalPairingCodeQuery::INCLUDE_QR_CODE : null];
    }
    protected function defaultPayload() : array
    {
        return ['profileId' => $this->profileId];
    }
    public function resolveResourcePath() : string
    {
        return 'terminals/pairing-codes';
    }
}
