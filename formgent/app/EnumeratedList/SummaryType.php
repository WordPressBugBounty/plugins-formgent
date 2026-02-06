<?php

namespace FormGent\App\EnumeratedList;

defined( 'ABSPATH' ) || exit;

class SummaryType extends EnumBase {
    const PAGINATION   = 'pagination';
    const CHART        = 'chart';
    const SINGLECHOICE = 'single_choice';
    const MULTICHOICE  = 'multi_choice';
    const HASONE       = 'has_one';
    const HASCHILD     = 'has_child';
    const CUSTOM       = 'custom';
}