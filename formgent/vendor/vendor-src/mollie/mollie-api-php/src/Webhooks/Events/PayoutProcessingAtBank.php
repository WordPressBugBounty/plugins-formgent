<?php

namespace FormGent\Mollie\Api\Webhooks\Events;

use FormGent\Mollie\Api\Webhooks\WebhookEventType;
class PayoutProcessingAtBank extends BaseEvent
{
    public static function type() : string
    {
        return WebhookEventType::PAYOUT_PROCESSING_AT_BANK;
    }
}
