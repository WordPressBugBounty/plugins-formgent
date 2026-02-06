<?php

namespace FormGent\CoreInterfaces\Core;

use FormGent\CoreInterfaces\Core\Request\RequestInterface;
use FormGent\CoreInterfaces\Core\Response\ResponseInterface;
interface ContextInterface
{
    public function getRequest() : RequestInterface;
    public function getResponse() : ResponseInterface;
}
