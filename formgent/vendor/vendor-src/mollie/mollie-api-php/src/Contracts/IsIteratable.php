<?php

namespace FormGent\Mollie\Api\Contracts;

interface IsIteratable
{
    public function iteratorEnabled() : bool;
    public function iteratesBackwards() : bool;
}
