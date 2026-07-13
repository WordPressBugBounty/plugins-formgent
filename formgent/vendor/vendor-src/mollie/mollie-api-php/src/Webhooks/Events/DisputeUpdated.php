<?php

namespace FormGent\Mollie\Api\Webhooks\Events;

use FormGent\Mollie\Api\Webhooks\WebhookEventType;
class DisputeUpdated extends BaseEvent
{
    public static function type() : string
    {
        return WebhookEventType::DISPUTE_UPDATED;
    }
}
