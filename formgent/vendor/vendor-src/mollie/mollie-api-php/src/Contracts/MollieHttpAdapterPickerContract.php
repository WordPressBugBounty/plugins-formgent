<?php

namespace FormGent\Mollie\Api\Contracts;

interface MollieHttpAdapterPickerContract
{
    /**
     * @param  \GuzzleHttp\ClientInterface|HttpAdapterContract  $httpClient
     */
    public function pickHttpAdapter($httpClient) : HttpAdapterContract;
}
