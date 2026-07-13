<?php

namespace FormGent\Mollie\Api\Webhooks\Events;

use FormGent\Mollie\Api\Webhooks\WebhookEventType;
class FileFailed extends BaseEvent
{
    public static function type() : string
    {
        return WebhookEventType::FILE_FAILED;
    }
}
