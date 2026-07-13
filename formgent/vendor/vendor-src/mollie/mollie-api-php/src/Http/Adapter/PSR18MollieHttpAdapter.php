<?php

namespace FormGent\Mollie\Api\Http\Adapter;

use FormGent\Mollie\Api\Contracts\HttpAdapterContract;
use FormGent\Mollie\Api\Exceptions\NetworkRequestException;
use FormGent\Mollie\Api\Exceptions\RequestException;
use FormGent\Mollie\Api\Exceptions\RetryableNetworkRequestException;
use FormGent\Mollie\Api\Http\PendingRequest;
use FormGent\Mollie\Api\Http\Response;
use FormGent\Mollie\Api\Utils\Factories;
use FormGent\Psr\Http\Client\ClientInterface;
use FormGent\Psr\Http\Client\NetworkExceptionInterface;
use FormGent\Psr\Http\Client\RequestExceptionInterface;
use FormGent\Psr\Http\Message\RequestFactoryInterface;
use FormGent\Psr\Http\Message\RequestInterface;
use FormGent\Psr\Http\Message\ResponseFactoryInterface;
use FormGent\Psr\Http\Message\ResponseInterface;
use FormGent\Psr\Http\Message\StreamFactoryInterface;
use FormGent\Psr\Http\Message\UriFactoryInterface;
use Throwable;
final class PSR18MollieHttpAdapter implements HttpAdapterContract
{
    private ClientInterface $httpClient;
    private RequestFactoryInterface $requestFactory;
    private ResponseFactoryInterface $responseFactory;
    private StreamFactoryInterface $streamFactory;
    private UriFactoryInterface $uriFactory;
    private ?Factories $factories = null;
    public function __construct(ClientInterface $httpClient, RequestFactoryInterface $requestFactory, ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory, UriFactoryInterface $uriFactory)
    {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
        $this->uriFactory = $uriFactory;
    }
    public function factories() : Factories
    {
        return $this->factories ??= new Factories($this->requestFactory, $this->responseFactory, $this->streamFactory, $this->uriFactory);
    }
    /**
     * Send a request using a PSR-18 compatible HTTP client.
     *
     * @throws NetworkRequestException When a network error occurs
     * @throws RetryableNetworkRequestException When a temporary network error occurs
     * @throws RequestException When the request fails with a response
     */
    public function sendRequest(PendingRequest $pendingRequest) : Response
    {
        $request = $pendingRequest->createPsrRequest();
        try {
            $response = $this->httpClient->sendRequest($request);
            return $this->createResponse($response, $request, $pendingRequest);
        } catch (NetworkExceptionInterface $e) {
            // PSR-18 NetworkExceptionInterface indicates network errors, which are retryable
            throw new RetryableNetworkRequestException($pendingRequest, 'Network error: ' . $e->getMessage());
        } catch (RequestExceptionInterface $e) {
            if (\method_exists($e, 'getResponse') && ($response = $e->getResponse())) {
                return $this->createResponse($response, $request, $pendingRequest, $e);
            }
            throw new RetryableNetworkRequestException($pendingRequest, 'Network error: ' . $e->getMessage());
        }
    }
    protected function createResponse(ResponseInterface $psrResponse, RequestInterface $psrRequest, PendingRequest $pendingRequest, ?Throwable $exception = null) : Response
    {
        return new Response($psrResponse, $psrRequest, $pendingRequest, $exception);
    }
    /**
     * Get the version string for the HTTP client implementation.
     * This is used in the User-Agent header.
     */
    public function version() : string
    {
        $clientClass = \get_class($this->httpClient);
        $clientName = \substr($clientClass, \strrpos($clientClass, '\\') + 1);
        return 'PSR18/' . $clientName;
    }
}
