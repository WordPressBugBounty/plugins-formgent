<?php

namespace FormGent\Mollie\Api\Contracts;

interface HasPayload
{
    public function payload() : PayloadRepository;
}
