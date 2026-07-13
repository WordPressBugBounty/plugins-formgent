<?php

namespace FormGent\Mollie\Api\Http\Data;

use FormGent\Mollie\Api\Contracts\Resolvable;
class ApplicationFee implements Resolvable
{
    public Money $amount;
    public string $description;
    public function __construct(Money $amount, string $description)
    {
        $this->amount = $amount;
        $this->description = $description;
    }
    public function toArray() : array
    {
        return ['amount' => $this->amount, 'description' => $this->description];
    }
}
