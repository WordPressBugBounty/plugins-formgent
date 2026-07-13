<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\HasPayload;
use FormGent\Mollie\Api\Contracts\SupportsTestmodeInPayload;
use FormGent\Mollie\Api\Http\Data\Money;
use FormGent\Mollie\Api\Resources\Payout;
use FormGent\Mollie\Api\Traits\HasJsonPayload;
use FormGent\Mollie\Api\Types\Method;
class CreatePayoutRequest extends ResourceHydratableRequest implements HasPayload, SupportsTestmodeInPayload
{
    use HasJsonPayload;
    /**
     * Define the HTTP method.
     */
    protected static string $method = Method::POST;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = Payout::class;
    private string $balanceId;
    private ?Money $amount;
    private ?string $description;
    public function __construct(string $balanceId, ?Money $amount = null, ?string $description = null)
    {
        $this->balanceId = $balanceId;
        $this->amount = $amount;
        $this->description = $description;
    }
    protected function defaultPayload() : array
    {
        return ['balanceId' => $this->balanceId, 'amount' => $this->amount, 'description' => $this->description];
    }
    /**
     * The resource path.
     */
    public function resolveResourcePath() : string
    {
        return 'payouts';
    }
}
