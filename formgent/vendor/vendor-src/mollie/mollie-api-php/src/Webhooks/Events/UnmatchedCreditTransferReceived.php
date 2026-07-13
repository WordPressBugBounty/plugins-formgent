<?php

namespace FormGent\Mollie\Api\Webhooks\Events;

use FormGent\Mollie\Api\Webhooks\WebhookEventType;
class UnmatchedCreditTransferReceived extends BaseEvent
{
    public static function type() : string
    {
        return WebhookEventType::UNMATCHED_CREDIT_TRANSFER_RECEIVED;
    }
}
