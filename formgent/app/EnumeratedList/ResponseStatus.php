<?php

namespace FormGent\App\EnumeratedList;

defined( 'ABSPATH' ) || exit;

class ResponseStatus extends EnumBase {
    const DRAFT   = 'draft';
    const PUBLISH = 'publish';
}