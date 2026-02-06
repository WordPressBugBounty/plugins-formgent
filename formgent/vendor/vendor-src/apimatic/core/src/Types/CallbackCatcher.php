<?php

declare (strict_types=1);
namespace FormGent\Core\Types;

use FormGent\CoreInterfaces\Core\ContextInterface;
use FormGent\CoreInterfaces\Core\Request\RequestInterface;
use FormGent\CoreInterfaces\Core\Response\ResponseInterface;
use FormGent\CoreInterfaces\Sdk\ConverterInterface;
use FormGent\Core\Types\Sdk\CoreCallback;
class CallbackCatcher extends CoreCallback
{
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var ResponseInterface
     */
    private $response;
    /**
     * Create instance
     */
    public function __construct()
    {
        $instance = $this;
        parent::__construct(null, function (ContextInterface $httpContext) use($instance) : void {
            $instance->request = $httpContext->getRequest();
            $instance->response = $httpContext->getResponse();
        });
    }
    /**
     * Get the Request object associated with this API call
     */
    public function getRequest() : RequestInterface
    {
        return $this->request;
    }
    /**
     * Get the Response object associated with this API call
     */
    public function getResponse() : ResponseInterface
    {
        return $this->response;
    }
    public function callOnBeforeWithConversion(RequestInterface $request, ConverterInterface $converter)
    {
        parent::callOnBeforeRequest($request);
    }
    public function callOnAfterWithConversion(ContextInterface $context, ConverterInterface $converter)
    {
        parent::callOnAfterRequest($context);
    }
}
