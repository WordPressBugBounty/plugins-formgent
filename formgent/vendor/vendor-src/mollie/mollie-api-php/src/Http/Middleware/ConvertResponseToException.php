<?php

namespace FormGent\Mollie\Api\Http\Middleware;

use FormGent\Mollie\Api\Contracts\ResponseMiddleware;
use FormGent\Mollie\Api\Exceptions\ApiException;
use FormGent\Mollie\Api\Exceptions\ForbiddenException;
use FormGent\Mollie\Api\Exceptions\MethodNotAllowedException;
use FormGent\Mollie\Api\Exceptions\NotFoundException;
use FormGent\Mollie\Api\Exceptions\RequestTimeoutException;
use FormGent\Mollie\Api\Exceptions\ServiceUnavailableException;
use FormGent\Mollie\Api\Exceptions\TooManyRequestsException;
use FormGent\Mollie\Api\Exceptions\UnauthorizedException;
use FormGent\Mollie\Api\Exceptions\ValidationException;
use FormGent\Mollie\Api\Http\Response;
use FormGent\Mollie\Api\Http\ResponseStatusCode;
class ConvertResponseToException implements ResponseMiddleware
{
    public function __invoke(Response $response) : void
    {
        if ($response->successful()) {
            return;
        }
        $status = $response->status();
        switch ($status) {
            case ResponseStatusCode::HTTP_UNAUTHORIZED:
                throw UnauthorizedException::fromResponse($response);
            case ResponseStatusCode::HTTP_FORBIDDEN:
                throw ForbiddenException::fromResponse($response);
            case ResponseStatusCode::HTTP_NOT_FOUND:
                throw NotFoundException::fromResponse($response);
            case ResponseStatusCode::HTTP_METHOD_NOT_ALLOWED:
                throw MethodNotAllowedException::fromResponse($response);
            case ResponseStatusCode::HTTP_REQUEST_TIMEOUT:
                throw RequestTimeoutException::fromResponse($response);
            case ResponseStatusCode::HTTP_UNPROCESSABLE_ENTITY:
                throw ValidationException::fromResponse($response);
            case ResponseStatusCode::HTTP_TOO_MANY_REQUESTS:
                throw TooManyRequestsException::fromResponse($response);
            case ResponseStatusCode::HTTP_SERVICE_UNAVAILABLE:
                throw ServiceUnavailableException::fromResponse($response);
            default:
                throw ApiException::fromResponse($response);
        }
    }
}
