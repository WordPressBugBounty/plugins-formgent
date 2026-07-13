<?php

namespace FormGent\Mollie\Api\Webhooks\Events;

use FormGent\Mollie\Api\Webhooks\WebhookEventType;
class DisputeCreated extends BaseEvent
{
    public static function type() : string
    {
        return WebhookEventType::DISPUTE_CREATED;
    }
}
