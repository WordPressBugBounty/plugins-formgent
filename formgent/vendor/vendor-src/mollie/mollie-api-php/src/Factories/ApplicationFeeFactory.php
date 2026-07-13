<?php

namespace FormGent\Mollie\Api\Factories;

use FormGent\Mollie\Api\Http\Data\ApplicationFee;
class ApplicationFeeFactory extends Factory
{
    public function create() : ApplicationFee
    {
        return new ApplicationFee(MoneyFactory::new($this->get('amount'))->create(), $this->get('description'));
    }
}
