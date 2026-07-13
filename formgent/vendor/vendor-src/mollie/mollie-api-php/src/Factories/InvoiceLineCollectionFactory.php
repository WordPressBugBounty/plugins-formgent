<?php

namespace FormGent\Mollie\Api\Factories;

use FormGent\Mollie\Api\Http\Data\DataCollection;
class InvoiceLineCollectionFactory extends Factory
{
    public function create() : DataCollection
    {
        return new DataCollection(\array_map(fn($item) => InvoiceLineFactory::new($item)->create(), $this->get()));
    }
}
