<?php

namespace FormGent\Mollie\Api\Webhooks\Events;

use FormGent\Mollie\Api\Webhooks\WebhookEventType;
class BusinessAccountTransferBlocked extends BaseEvent
{
    public static function type() : string
    {
        return WebhookEventType::BUSINESS_ACCOUNT_TRANSFER_BLOCKED;
    }
}
