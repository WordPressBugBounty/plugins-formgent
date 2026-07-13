<?php

namespace FormGent\Mollie\Api\Webhooks\Events;

use FormGent\Mollie\Api\Webhooks\WebhookEventType;
class PaymentLinkPaid extends BaseEvent
{
    public static function type() : string
    {
        return WebhookEventType::PAYMENT_LINK_PAID;
    }
}
