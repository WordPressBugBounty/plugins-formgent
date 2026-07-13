<?php

namespace FormGent\Mollie\Api\Webhooks\Events;

use FormGent\Mollie\Api\Webhooks\WebhookEventType;
class BalanceTransactionCreated extends BaseEvent
{
    public static function type() : string
    {
        return WebhookEventType::BALANCE_TRANSACTION_CREATED;
    }
}
