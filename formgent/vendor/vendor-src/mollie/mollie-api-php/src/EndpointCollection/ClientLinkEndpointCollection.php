<?php

namespace FormGent\Mollie\Api\EndpointCollection;

use FormGent\Mollie\Api\Exceptions\RequestException;
use FormGent\Mollie\Api\Factories\CreateClientLinkRequestFactory;
use FormGent\Mollie\Api\Resources\ClientLink;
class ClientLinkEndpointCollection extends EndpointCollection
{
    /**
     * Creates a client link in Mollie.
     *
     * @param  array  $payload  An array containing details on the client link.
     *
     * @throws RequestException
     */
    public function create(array $payload = []) : ClientLink
    {
        $request = CreateClientLinkRequestFactory::new()->withPayload($payload)->create();
        /** @var ClientLink */
        return $this->send($request);
    }
}
