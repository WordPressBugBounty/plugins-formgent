<?php

namespace FormGent\Mollie\Api\Webhooks\Events;

use FormGent\Mollie\Api\Webhooks\WebhookEventType;
class PayoutCanceled extends BaseEvent
{
    public static function type() : string
    {
        return WebhookEventType::PAYOUT_CANCELED;
    }
}
