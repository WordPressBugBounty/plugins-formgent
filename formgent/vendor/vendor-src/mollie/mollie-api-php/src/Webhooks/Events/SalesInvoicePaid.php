<?php

namespace FormGent\Mollie\Api\Webhooks\Events;

use FormGent\Mollie\Api\Webhooks\WebhookEventType;
class SalesInvoicePaid extends BaseEvent
{
    public static function type() : string
    {
        return WebhookEventType::SALES_INVOICE_PAID;
    }
}
