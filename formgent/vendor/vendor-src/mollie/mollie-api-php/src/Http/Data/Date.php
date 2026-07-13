<?php

namespace FormGent\Mollie\Api\Http\Data;

class Date extends Temporal
{
    public const FORMAT = 'Y-m-d';
    protected function getFormat() : string
    {
        return self::FORMAT;
    }
}
