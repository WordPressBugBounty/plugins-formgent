<?php

namespace FormGent\Mollie\Api\Http;

use FormGent\Mollie\Api\Contracts\Connector;
use FormGent\Mollie\Api\Contracts\IsResponseAware;
use FormGent\Mollie\Api\Contracts\PayloadRepository;
use FormGent\Mollie\Api\Exceptions\MollieException;
use FormGent\Mollie\Api\Http\Auth\ApiKeyAuthenticator;
use FormGent\Mollie\Api\Http\Middleware\ApplyIdempotencyKey;
use FormGent\Mollie\Api\Http\Middleware\ConvertResponseToException;
use FormGent\Mollie\Api\Http\Middleware\Hydrate;
use FormGent\Mollie\Api\Http\Middleware\MiddlewarePriority;
use FormGent\Mollie\Api\Http\Middleware\ResetIdempotencyKey;
use FormGent\Mollie\Api\Http\PendingRequest\AuthenticateRequest;
use FormGent\Mollie\Api\Http\PendingRequest\HandleTestmode;
use FormGent\Mollie\Api\Http\PendingRequest\MergeBody;
use FormGent\Mollie\Api\Http\PendingRequest\MergeRequestProperties;
use FormGent\Mollie\Api\Http\PendingRequest\SetUserAgent;
use FormGent\Mollie\Api\Traits\HasMiddleware;
use FormGent\Mollie\Api\Traits\HasRequestProperties;
use FormGent\Mollie\Api\Traits\ManagesPsrRequests;
use FormGent\Mollie\Api\Utils\Url;
class PendingRequest
{
    use HasMiddleware;
    use HasRequestProperties;
    use ManagesPsrRequests;
    protected Connector $connector;
    protected Request $request;
    protected ?PayloadRepository $payload = null;
    protected string $method;
    /**
     * The URL the request will be made to.
     */
    protected string $url;
    public function __construct(Connector $connector, Request $request)
    {
        $this->factoryCollection = $connector->getHttpClient()->factories();
        $this->connector = $connector;
        $this->request = $request;
        $this->method = $request->getMethod();
        $this->url = Url::join($connector->resolveBaseUrl(), $request->resolveResourcePath());
        $this->tap(new SetUserAgent())->tap(new MergeRequestProperties())->tap(new MergeBody())->tap(new AuthenticateRequest())->tap(new HandleTestmode());
        $this->middleware()->onRequest(new ApplyIdempotencyKey(), 'idempotency')->onResponse(new ResetIdempotencyKey(), 'idempotency')->onResponse(new ConvertResponseToException(), MiddlewarePriority::HIGH)->onResponse(new Hydrate(), 'hydrate', MiddlewarePriority::LOW)->merge($connector->middleware(), $request->middleware());
    }
    /**
     * We are returning on whether the request is actually
     * made in testmode and not if the request is sent with a
     * testmode parameter. This allows the developer to react to requests
     * being made in testmode independent of the testmode parameter being set.
     */
    public function getTestmode() : bool
    {
        if ($this->connector->getTestmode() || $this->request->getTestmode()) {
            return \true;
        }
        $authenticator = $this->connector->getAuthenticator();
        if (!$authenticator instanceof ApiKeyAuthenticator) {
            return \false;
        }
        return $authenticator->isTestToken();
    }
    public function setPayload(PayloadRepository $bodyRepository) : self
    {
        $this->payload = $bodyRepository;
        return $this;
    }
    public function payload() : ?PayloadRepository
    {
        return $this->payload;
    }
    public function url() : string
    {
        return $this->url;
    }
    public function method() : string
    {
        return $this->method;
    }
    public function getConnector() : Connector
    {
        return $this->connector;
    }
    public function getRequest() : Request
    {
        return $this->request;
    }
    public function executeRequestHandlers() : self
    {
        return $this->middleware()->executeOnRequest($this);
    }
    /**
     * @return Response|IsResponseAware
     */
    public function executeResponseHandlers(Response $response)
    {
        return $this->middleware()->executeOnResponse($response);
    }
    public function executeFatalHandlers(MollieException $exception) : MollieException
    {
        return $this->middleware()->executeOnFatal($exception);
    }
    protected function tap(callable $callable) : self
    {
        $callable($this);
        return $this;
    }
}
