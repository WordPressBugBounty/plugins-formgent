<?php

namespace FormGent\Mollie\Api\Exceptions;

use FormGent\Mollie\Api\Http\PendingRequest;
use FormGent\Mollie\Api\Http\Response;
use FormGent\Psr\Http\Client\RequestExceptionInterface;
use FormGent\Psr\Http\Message\RequestInterface;
use Throwable;
class RequestException extends MollieException implements RequestExceptionInterface
{
    protected Response $response;
    public function __construct(Response $response, ?string $message = null, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }
    public function getRequest() : RequestInterface
    {
        return $this->response->getPsrRequest();
    }
    public function getResponse() : Response
    {
        return $this->response;
    }
    public function getPendingRequest() : PendingRequest
    {
        return $this->response->getPendingRequest();
    }
    public function getStatusCode() : int
    {
        return $this->response->status();
    }
}
