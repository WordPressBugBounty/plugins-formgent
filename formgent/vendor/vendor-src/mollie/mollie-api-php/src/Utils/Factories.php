<?php

namespace FormGent\Mollie\Api\Utils;

use FormGent\Psr\Http\Message\RequestFactoryInterface;
use FormGent\Psr\Http\Message\ResponseFactoryInterface;
use FormGent\Psr\Http\Message\StreamFactoryInterface;
use FormGent\Psr\Http\Message\UriFactoryInterface;
class Factories
{
    public RequestFactoryInterface $requestFactory;
    public ResponseFactoryInterface $responseFactory;
    public StreamFactoryInterface $streamFactory;
    public UriFactoryInterface $uriFactory;
    public function __construct(RequestFactoryInterface $requestFactory, ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory, UriFactoryInterface $uriFactory)
    {
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
        $this->uriFactory = $uriFactory;
    }
}
