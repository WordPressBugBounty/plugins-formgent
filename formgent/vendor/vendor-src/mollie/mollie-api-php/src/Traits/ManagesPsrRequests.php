<?php

namespace FormGent\Mollie\Api\Traits;

use FormGent\Mollie\Api\Contracts\PayloadRepository;
use FormGent\Mollie\Api\Http\PendingRequest;
use FormGent\Mollie\Api\Utils\Factories;
use FormGent\Mollie\Api\Utils\Url;
use FormGent\Psr\Http\Message\RequestInterface;
use FormGent\Psr\Http\Message\UriInterface;
/**
 * @mixin PendingRequest
 */
trait ManagesPsrRequests
{
    protected Factories $factoryCollection;
    public function createPsrRequest() : RequestInterface
    {
        $factories = $this->factoryCollection;
        $request = $factories->requestFactory->createRequest($this->method(), $this->getUri());
        foreach ($this->headers()->all() as $headerName => $headerValue) {
            $request = $request->withHeader($headerName, $headerValue);
        }
        $payload = $this->payload();
        if ($payload instanceof PayloadRepository) {
            $request = $request->withBody($payload->toStream($factories->streamFactory));
        }
        return $request;
    }
    public function getUri() : UriInterface
    {
        $uri = $this->factoryCollection->uriFactory->createUri($this->url());
        $existingQuery = Url::parseQuery($uri->getQuery());
        return $uri->withQuery(\http_build_query(\array_merge($existingQuery, $this->query()->all())));
    }
    /**
     * Get the factory collection
     */
    public function getFactoryCollection() : Factories
    {
        return $this->factoryCollection;
    }
}
