<?php

namespace FormGent\Mollie\Api\Http\Data;

use FormGent\Mollie\Api\Contracts\Arrayable;
use FormGent\Mollie\Api\Traits\ComposableFromArray;
class Discount implements Arrayable
{
    use ComposableFromArray;
    public string $type;
    public string $value;
    public function __construct(string $type, string $value)
    {
        $this->type = $type;
        $this->value = $value;
    }
    public function toArray() : array
    {
        return ['type' => $this->type, 'value' => $this->value];
    }
}
