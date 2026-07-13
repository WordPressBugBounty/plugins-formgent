<?php

namespace FormGent\Mollie\Api\Factories;

use FormGent\Mollie\Api\Http\Data\Date;
use FormGent\Mollie\Api\Http\Requests\CreateSubscriptionRequest;
class CreateSubscriptionRequestFactory extends RequestFactory
{
    private string $customerId;
    public function __construct(string $customerId)
    {
        $this->customerId = $customerId;
    }
    public function create() : CreateSubscriptionRequest
    {
        return new CreateSubscriptionRequest($this->customerId, MoneyFactory::new($this->payload('amount'))->create(), $this->payload('interval'), $this->payload('description'), $this->payload('status'), $this->payload('times'), $this->transformFromPayload('startDate', fn(string $date) => new Date($date), Date::class), $this->payload('method'), $this->transformFromPayload('applicationFee', fn($fee) => ApplicationFeeFactory::new($fee)->create()), $this->payload('metadata'), $this->payload('webhookUrl'), $this->payload('mandateId'), $this->payload('profileId'));
    }
}
