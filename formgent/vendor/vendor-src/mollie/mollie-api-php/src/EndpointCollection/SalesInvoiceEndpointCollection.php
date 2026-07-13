<?php

namespace FormGent\Mollie\Api\EndpointCollection;

use FormGent\Mollie\Api\Exceptions\RequestException;
use FormGent\Mollie\Api\Factories\CreateSalesInvoiceRequestFactory;
use FormGent\Mollie\Api\Factories\UpdateSalesInvoiceRequestFactory;
use FormGent\Mollie\Api\Http\Requests\DeleteSalesInvoiceRequest;
use FormGent\Mollie\Api\Http\Requests\GetPaginatedSalesInvoicesRequest;
use FormGent\Mollie\Api\Http\Requests\GetSalesInvoiceRequest;
use FormGent\Mollie\Api\Resources\LazyCollection;
use FormGent\Mollie\Api\Resources\SalesInvoice;
use FormGent\Mollie\Api\Resources\SalesInvoiceCollection;
class SalesInvoiceEndpointCollection extends EndpointCollection
{
    /**
     * Retrieve a SalesInvoice from Mollie.
     *
     * @throws RequestException
     */
    public function get(string $id) : SalesInvoice
    {
        return $this->send(new GetSalesInvoiceRequest($id));
    }
    /**
     * Creates a SalesInvoice in Mollie.
     *
     * @throws RequestException
     */
    public function create(array $payload = []) : SalesInvoice
    {
        $request = CreateSalesInvoiceRequestFactory::new()->withPayload($payload)->create();
        return $this->send($request);
    }
    /**
     * Update a specific SalesInvoice resource.
     *
     * @throws RequestException
     */
    public function update(string $id, array $payload = []) : ?SalesInvoice
    {
        $request = UpdateSalesInvoiceRequestFactory::new($id)->withPayload($payload)->create();
        return $this->send($request);
    }
    /**
     * Delete a SalesInvoice from Mollie.
     *
     * @throws RequestException
     */
    public function delete(string $id) : void
    {
        $this->send(new DeleteSalesInvoiceRequest($id));
    }
    /**
     * Retrieves a collection of SalesInvoices from Mollie.
     *
     * @throws RequestException
     */
    public function page(?string $from = null, ?int $limit = null) : SalesInvoiceCollection
    {
        return $this->send(new GetPaginatedSalesInvoicesRequest($from, $limit));
    }
    /**
     * Create an iterator for iterating over sales invoices retrieved from Mollie.
     *
     * @param  string|null  $from  The first resource ID you want to include in your list.
     * @param  bool  $iterateBackwards  Set to true for reverse order iteration (default is false).
     */
    public function iterator(?string $from = null, ?int $limit = null, bool $iterateBackwards = \false) : LazyCollection
    {
        return $this->send((new GetPaginatedSalesInvoicesRequest($from, $limit))->useIterator()->setIterationDirection($iterateBackwards));
    }
}
