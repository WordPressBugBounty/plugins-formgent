<?php

namespace FormGent\CoreInterfaces\Sdk;

use FormGent\CoreInterfaces\Core\ContextInterface;
use FormGent\CoreInterfaces\Core\Request\RequestInterface;
use FormGent\CoreInterfaces\Core\Response\ResponseInterface;
interface ConverterInterface
{
    public function createApiException(string $message, RequestInterface $request, ?ResponseInterface $response);
    public function createHttpContext(ContextInterface $context);
    public function createHttpRequest(RequestInterface $request);
    public function createHttpResponse(ResponseInterface $response);
    public function createApiResponse(ContextInterface $context, $deserializedBody);
    public function createFileWrapper(string $realFilePath, ?string $mimeType, ?string $filename);
}
