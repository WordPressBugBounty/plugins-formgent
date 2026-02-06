<?php

namespace FormGent\Core\Logger;

use FormGent\CoreInterfaces\Core\Logger\ApiLoggerInterface;
use FormGent\CoreInterfaces\Core\Request\RequestInterface;
use FormGent\CoreInterfaces\Core\Response\ResponseInterface;
class NullApiLogger implements ApiLoggerInterface
{
    /**
     * @inheritDoc
     */
    public function logRequest(RequestInterface $request) : void
    {
        // noop
    }
    /**
     * @inheritDoc
     */
    public function logResponse(ResponseInterface $response) : void
    {
        // noop
    }
}
