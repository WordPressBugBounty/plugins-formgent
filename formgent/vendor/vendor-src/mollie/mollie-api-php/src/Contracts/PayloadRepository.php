<?php

namespace FormGent\Mollie\Api\Contracts;

use FormGent\Psr\Http\Message\StreamFactoryInterface;
use FormGent\Psr\Http\Message\StreamInterface;
interface PayloadRepository extends Repository
{
    /**
     * Convert the repository contents into a stream
     */
    public function toStream(StreamFactoryInterface $streamFactory) : StreamInterface;
}
