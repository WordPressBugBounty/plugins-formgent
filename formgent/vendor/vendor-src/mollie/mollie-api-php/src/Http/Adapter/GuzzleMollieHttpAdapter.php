<?php

namespace FormGent\Mollie\Api\Http\Adapter;

use FormGent\Composer\CaBundle\CaBundle;
use FormGent\GuzzleHttp\Client;
use FormGent\GuzzleHttp\ClientInterface;
use FormGent\GuzzleHttp\Exception\ConnectException;
use FormGent\GuzzleHttp\Exception\RequestException;
use FormGent\GuzzleHttp\Exception\TooManyRedirectsException;
use FormGent\GuzzleHttp\HandlerStack;
use FormGent\GuzzleHttp\Psr7\HttpFactory;
use FormGent\GuzzleHttp\RequestOptions;
use FormGent\Mollie\Api\Contracts\HttpAdapterContract;
use FormGent\Mollie\Api\Exceptions\NetworkRequestException;
use FormGent\Mollie\Api\Exceptions\RetryableNetworkRequestException;
use FormGent\Mollie\Api\Http\PendingRequest;
use FormGent\Mollie\Api\Http\Response;
use FormGent\Mollie\Api\Utils\Factories;
use FormGent\Psr\Http\Message\RequestInterface;
use FormGent\Psr\Http\Message\ResponseInterface;
use Throwable;
final class GuzzleMollieHttpAdapter implements HttpAdapterContract
{
    /**
     * Default response timeout (in seconds).
     */
    public const DEFAULT_TIMEOUT = 10;
    /**
     * Default connect timeout (in seconds).
     */
    public const DEFAULT_CONNECT_TIMEOUT = 2;
    protected ClientInterface $httpClient;
    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
    public function factories() : Factories
    {
        $factory = new HttpFactory();
        return new Factories($factory, $factory, $factory, $factory);
    }
    /**
     * Create a preconfigured Guzzle adapter.
     */
    public static function createClient() : self
    {
        $handlerStack = HandlerStack::create();
        $client = new Client([RequestOptions::VERIFY => CaBundle::getBundledCaBundlePath(), RequestOptions::TIMEOUT => self::DEFAULT_TIMEOUT, RequestOptions::CONNECT_TIMEOUT => self::DEFAULT_CONNECT_TIMEOUT, RequestOptions::HTTP_ERRORS => \false, 'handler' => $handlerStack]);
        return new GuzzleMollieHttpAdapter($client);
    }
    /**
     * @throws NetworkRequestException
     * @throws RetryableNetworkRequestException
     */
    public function sendRequest(PendingRequest $pendingRequest) : Response
    {
        $request = $pendingRequest->createPsrRequest();
        try {
            $response = $this->httpClient->send($request);
            return $this->createResponse($response, $request, $pendingRequest);
        } catch (ConnectException $e) {
            throw new RetryableNetworkRequestException($pendingRequest, $e->getMessage());
        } catch (TooManyRedirectsException $e) {
            throw new NetworkRequestException($pendingRequest, $e, $e->getMessage());
        } catch (RequestException $e) {
            if ($response = $e->getResponse()) {
                return $this->createResponse($response, $request, $pendingRequest, $e);
            }
            throw new RetryableNetworkRequestException($pendingRequest, $e->getMessage());
        }
    }
    protected function createResponse(ResponseInterface $psrResponse, RequestInterface $psrRequest, PendingRequest $pendingRequest, ?Throwable $exception = null) : Response
    {
        return new Response($psrResponse, $psrRequest, $pendingRequest, $exception);
    }
    public function version() : string
    {
        return 'Guzzle/' . ClientInterface::MAJOR_VERSION;
    }
}
