<?php

namespace FormGent\Mollie\Api\Factories;

use FormGent\Mollie\Api\Http\Data\Address;
use FormGent\Mollie\Api\Http\Requests\CreatePaymentRequest;
use FormGent\Mollie\Api\Types\PaymentQuery;
use FormGent\Mollie\Api\Utils\Utility;
class CreatePaymentRequestFactory extends RequestFactory
{
    public function create() : CreatePaymentRequest
    {
        $includeQrCode = $this->queryIncludes('include', PaymentQuery::INCLUDE_QR_CODE);
        return new CreatePaymentRequest($this->payload('description'), MoneyFactory::new($this->payload('amount'))->create(), $this->payload('redirectUrl'), $this->payload('cancelUrl'), $this->payload('webhookUrl'), $this->transformFromPayload('lines', fn($items) => OrderLineCollectionFactory::new($items)->create()), $this->transformFromPayload('billingAddress', fn($item) => Address::fromArray($item)), $this->transformFromPayload('shippingAddress', fn($item) => Address::fromArray($item)), $this->payload('locale'), $this->payload('method'), $this->payload('issuer'), $this->payload('restrictPaymentMethodsToCountry'), $this->payload('metadata'), $this->payload('captureMode'), $this->payload('captureDelay'), $this->transformFromPayload('applicationFee', fn($item) => ApplicationFeeFactory::new($item)->create()), $this->transformFromPayload('routing', fn($items) => PaymentRouteCollectionFactory::new($items)->create()), $this->payload('sequenceType'), $this->payload('mandateId'), $this->payload('customerId'), $this->payload('profileId'), ($this->payload('additional') ?: Utility::filterByProperties(CreatePaymentRequest::class, $this->payload())) ?: [], $this->query('includeQrCode', $includeQrCode));
    }
}
