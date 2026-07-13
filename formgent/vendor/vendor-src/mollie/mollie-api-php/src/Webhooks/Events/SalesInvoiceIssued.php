<?php

namespace FormGent\Mollie\Api\Webhooks\Events;

use FormGent\Mollie\Api\Webhooks\WebhookEventType;
class SalesInvoiceIssued extends BaseEvent
{
    public static function type() : string
    {
        return WebhookEventType::SALES_INVOICE_ISSUED;
    }
}
