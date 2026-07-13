<?php

namespace FormGent\Mollie\Api\Contracts;

interface IdempotencyContract
{
    public function getIdempotencyKey() : ?string;
    public function resetIdempotencyKey() : self;
    public function getIdempotencyKeyGenerator() : ?IdempotencyKeyGeneratorContract;
}
