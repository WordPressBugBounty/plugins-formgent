<?php

namespace FormGent\Mollie\Api\Webhooks\Events;

use FormGent\Mollie\Api\Webhooks\WebhookEventType;
class PayoutInitiated extends BaseEvent
{
    public static function type() : string
    {
        return WebhookEventType::PAYOUT_INITIATED;
    }
}
