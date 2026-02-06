<?php

namespace FormGent\App\EnumeratedList;

defined( 'ABSPATH' ) || exit;

class BackgroundProcessStatus extends EnumBase {
    const IDLE        = 'idle';
    const INITIALIZED = 'initialized';
    const RUNNING     = 'running';
    const CANCELLED   = 'cancelled';
    const FAILED      = 'failed';
    const DONE        = 'done';
}