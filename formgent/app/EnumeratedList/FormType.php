<?php

namespace FormGent\App\EnumeratedList;

defined( 'ABSPATH' ) || exit;

class FormType extends EnumBase {
    const GENERAL        = 'general';
    const CONVERSATIONAL = 'conversational';
}