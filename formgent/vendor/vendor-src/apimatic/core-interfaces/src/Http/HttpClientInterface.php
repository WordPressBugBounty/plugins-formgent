<?php

namespace FormGent\CoreInterfaces\Http;

use FormGent\CoreInterfaces\Core\Request\RequestInterface;
use FormGent\CoreInterfaces\Core\Response\ResponseInterface;
interface HttpClientInterface
{
    /**
     * Sends request and receive response from server.
     *
     * @param RequestInterface $request Request to be sent
     *
     * @return ResponseInterface
     */
    public function execute(RequestInterface $request) : ResponseInterface;
}
