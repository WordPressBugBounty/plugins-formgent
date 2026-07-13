<?php

namespace FormGent\Mollie\Api\Contracts;

interface Testable
{
    public function getTestmode() : ?bool;
}
