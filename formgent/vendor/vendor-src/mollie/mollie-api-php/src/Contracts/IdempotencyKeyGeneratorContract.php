<?php

namespace FormGent\Mollie\Api\Contracts;

interface IdempotencyKeyGeneratorContract
{
    public function generate() : string;
}
