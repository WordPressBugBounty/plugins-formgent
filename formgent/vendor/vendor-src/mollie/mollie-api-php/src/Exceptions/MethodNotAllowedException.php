<?php

namespace FormGent\Mollie\Api\Exceptions;

use FormGent\Mollie\Api\Http\Response;
use FormGent\Mollie\Api\Http\ResponseStatusCode;
class MethodNotAllowedException extends ApiException
{
    public static function fromResponse(Response $response) : self
    {
        $body = $response->json();
        return new self($response, 'The HTTP method is not supported. ' . \sprintf('Error executing API call (%d: %s): %s', ResponseStatusCode::HTTP_METHOD_NOT_ALLOWED, $body->title, $body->detail), ResponseStatusCode::HTTP_METHOD_NOT_ALLOWED);
    }
}
