<?php

namespace FormGent\App\EnumeratedList;

defined( 'ABSPATH' ) || exit;

/**
 * Enum for Queue Statuses
 */
class QueueStatus extends EnumBase {
    const IN_QUEUE    = 'in_queue';
    const IN_PROGRESS = 'in_progress';
    const COMPLETED   = 'completed';
    const SKIPPED     = 'skipped';
    const FAILED      = 'failed';
}
