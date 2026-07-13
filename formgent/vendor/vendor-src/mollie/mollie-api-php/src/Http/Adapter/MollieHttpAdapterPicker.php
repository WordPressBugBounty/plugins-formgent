<?php

namespace FormGent\Mollie\Api\Http\Adapter;

use FormGent\Mollie\Api\Contracts\HttpAdapterContract;
use FormGent\Mollie\Api\Contracts\MollieHttpAdapterPickerContract;
use FormGent\Mollie\Api\Exceptions\UnrecognizedClientException;
class MollieHttpAdapterPicker implements MollieHttpAdapterPickerContract
{
    /**
     * @param  \GuzzleHttp\ClientInterface|HttpAdapterContract|null|\stdClass  $httpClient
     *
     * @throws \Mollie\Api\Exceptions\UnrecognizedClientException
     */
    public function pickHttpAdapter($httpClient) : HttpAdapterContract
    {
        if (!$httpClient) {
            return $this->createDefaultAdapter();
        }
        if ($httpClient instanceof HttpAdapterContract) {
            return $httpClient;
        }
        if ($httpClient instanceof \FormGent\GuzzleHttp\ClientInterface) {
            return new GuzzleMollieHttpAdapter($httpClient);
        }
        throw new UnrecognizedClientException('The provided http client or adapter was not recognized.');
    }
    private function createDefaultAdapter() : HttpAdapterContract
    {
        if ($this->guzzleIsDetected()) {
            return GuzzleMollieHttpAdapter::createClient();
        }
        return new CurlMollieHttpAdapter();
    }
    private function guzzleIsDetected() : bool
    {
        return \interface_exists('\\' . \FormGent\GuzzleHttp\ClientInterface::class);
    }
}
