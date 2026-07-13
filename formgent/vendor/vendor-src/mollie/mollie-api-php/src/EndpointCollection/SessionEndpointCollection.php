<?php

namespace FormGent\Mollie\Api\EndpointCollection;

use FormGent\Mollie\Api\Exceptions\RequestException;
use FormGent\Mollie\Api\Factories\CreateSessionRequestFactory;
use FormGent\Mollie\Api\Http\Requests\GetSessionRequest;
use FormGent\Mollie\Api\Resources\Session;
class SessionEndpointCollection extends EndpointCollection
{
    /**
     * Retrieve a single session from Mollie.
     *
     * @throws RequestException
     */
    public function get(string $sessionId) : Session
    {
        /** @var Session */
        return $this->send(new GetSessionRequest($sessionId));
    }
    /**
     * Creates a session in Mollie.
     *
     * @throws RequestException
     */
    public function create(array $payload = []) : Session
    {
        $request = CreateSessionRequestFactory::new()->withPayload($payload)->create();
        /** @var Session */
        return $this->send($request);
    }
}
