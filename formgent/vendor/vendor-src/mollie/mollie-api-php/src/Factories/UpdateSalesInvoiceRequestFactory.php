<?php

namespace FormGent\Mollie\Api\Factories;

use FormGent\Mollie\Api\Http\Data\Discount;
use FormGent\Mollie\Api\Http\Data\EmailDetails;
use FormGent\Mollie\Api\Http\Data\PaymentDetails;
use FormGent\Mollie\Api\Http\Requests\UpdateSalesInvoiceRequest;
class UpdateSalesInvoiceRequestFactory extends RequestFactory
{
    private string $id;
    public function __construct(string $id)
    {
        $this->id = $id;
    }
    public function create() : UpdateSalesInvoiceRequest
    {
        return new UpdateSalesInvoiceRequest($this->id, $this->payload('status'), $this->payload('recipientIdentifier'), $this->payload('memo'), $this->payload('paymentTerm'), $this->transformFromPayload('paymentDetails', fn($data) => PaymentDetails::fromArray($data)), $this->transformFromPayload('emailDetails', fn($data) => EmailDetails::fromArray($data)), $this->transformFromPayload('recipient', fn($data) => RecipientFactory::new($data)->create()), $this->transformFromPayload('lines', fn(array $items) => InvoiceLineCollectionFactory::new($items)->create()), $this->payload('webhookUrl'), $this->transformFromPayload('discount', fn($data) => Discount::fromArray($data)), $this->payload('isEInvoice'));
    }
}
