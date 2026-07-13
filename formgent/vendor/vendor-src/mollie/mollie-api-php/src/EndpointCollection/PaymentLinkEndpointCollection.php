<?php

namespace FormGent\Mollie\Api\EndpointCollection;

use FormGent\Mollie\Api\Exceptions\RequestException;
use FormGent\Mollie\Api\Factories\CreatePaymentLinkRequestFactory;
use FormGent\Mollie\Api\Factories\UpdatePaymentLinkRequestFactory;
use FormGent\Mollie\Api\Http\Requests\DeletePaymentLinkRequest;
use FormGent\Mollie\Api\Http\Requests\GetPaginatedPaymentLinksRequest;
use FormGent\Mollie\Api\Http\Requests\GetPaymentLinkRequest;
use FormGent\Mollie\Api\Resources\LazyCollection;
use FormGent\Mollie\Api\Resources\PaymentLink;
use FormGent\Mollie\Api\Resources\PaymentLinkCollection;
use FormGent\Mollie\Api\Utils\Utility;
class PaymentLinkEndpointCollection extends EndpointCollection
{
    /**
     * Creates a payment link in Mollie.
     *
     * @throws RequestException
     */
    public function create(array $payload = [], bool $testmode = \false) : PaymentLink
    {
        $request = CreatePaymentLinkRequestFactory::new()->withPayload($payload)->create();
        /** @var PaymentLink */
        return $this->send($request->test($testmode));
    }
    /**
     * Retrieve a payment link from Mollie.
     *
     * Will throw an ApiException if the payment link id is invalid or the resource cannot be found.
     *
     * @param  bool|array  $testmode
     *
     * @throws RequestException
     */
    public function get(string $paymentLinkId, $testmode = \false) : PaymentLink
    {
        $testmode = Utility::extractBool($testmode, 'testmode', \false);
        /** @var PaymentLink */
        return $this->send((new GetPaymentLinkRequest($paymentLinkId))->test($testmode));
    }
    /**
     * Update a Payment Link.
     *
     * @throws RequestException
     */
    public function update(string $paymentLinkId, array $payload = [], bool $testmode = \false) : PaymentLink
    {
        $request = UpdatePaymentLinkRequestFactory::new($paymentLinkId)->withPayload($payload)->create();
        /** @var PaymentLink */
        return $this->send($request->test($testmode));
    }
    /**
     * Delete a Payment Link.
     *
     * @throws RequestException
     */
    public function delete(string $paymentLinkId, bool $testmode = \false) : void
    {
        $this->send((new DeletePaymentLinkRequest($paymentLinkId))->test($testmode));
    }
    /**
     * Retrieves a collection of Payment Links from Mollie.
     *
     * @param  string|null  $from  The first payment link ID you want to include in your list.
     * @param  bool|array  $testmode
     *
     * @throws RequestException
     */
    public function page(?string $from = null, ?int $limit = null, $testmode = \false) : PaymentLinkCollection
    {
        $testmode = Utility::extractBool($testmode, 'testmode', \false);
        /** @var PaymentLinkCollection */
        return $this->send((new GetPaginatedPaymentLinksRequest($from, $limit))->test($testmode));
    }
    /**
     * Create an iterator for iterating over payment links retrieved from Mollie.
     *
     * @param  string|null  $from  The first resource ID you want to include in your list.
     * @param  bool  $iterateBackwards  Set to true for reverse order iteration (default is false).
     */
    public function iterator(?string $from = null, ?int $limit = null, bool $testmode = \false, bool $iterateBackwards = \false) : LazyCollection
    {
        $testmode = Utility::extractBool($testmode, 'testmode', \false);
        return $this->send((new GetPaginatedPaymentLinksRequest($from, $limit))->useIterator()->setIterationDirection($iterateBackwards)->test($testmode));
    }
}
