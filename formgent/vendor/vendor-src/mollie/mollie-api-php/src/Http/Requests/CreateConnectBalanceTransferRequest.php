<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\HasPayload;
use FormGent\Mollie\Api\Contracts\SupportsTestmodeInPayload;
use FormGent\Mollie\Api\Http\Data\Money;
use FormGent\Mollie\Api\Http\Data\TransferParty;
use FormGent\Mollie\Api\Resources\ConnectBalanceTransfer;
use FormGent\Mollie\Api\Traits\HasJsonPayload;
use FormGent\Mollie\Api\Types\Method;
class CreateConnectBalanceTransferRequest extends ResourceHydratableRequest implements HasPayload, SupportsTestmodeInPayload
{
    use HasJsonPayload;
    /**
     * Define the HTTP method.
     */
    protected static string $method = Method::POST;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = ConnectBalanceTransfer::class;
    private Money $amount;
    private string $description;
    private TransferParty $source;
    private TransferParty $destination;
    private string $category;
    private ?array $metadata;
    public function __construct(Money $amount, string $description, TransferParty $source, TransferParty $destination, string $category, ?array $metadata = null)
    {
        $this->amount = $amount;
        $this->description = $description;
        $this->source = $source;
        $this->destination = $destination;
        $this->category = $category;
        $this->metadata = $metadata;
    }
    protected function defaultPayload() : array
    {
        return ['amount' => $this->amount, 'description' => $this->description, 'source' => $this->source, 'destination' => $this->destination, 'category' => $this->category, 'metadata' => $this->metadata];
    }
    /**
     * The resource path.
     */
    public function resolveResourcePath() : string
    {
        return 'connect/balance-transfers';
    }
}
