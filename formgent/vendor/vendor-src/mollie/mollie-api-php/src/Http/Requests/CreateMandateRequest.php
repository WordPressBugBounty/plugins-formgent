<?php

namespace FormGent\Mollie\Api\Http\Requests;

use DateTimeInterface;
use FormGent\Mollie\Api\Contracts\HasPayload;
use FormGent\Mollie\Api\Contracts\SupportsTestmodeInPayload;
use FormGent\Mollie\Api\Http\Data\Date;
use FormGent\Mollie\Api\Resources\Mandate;
use FormGent\Mollie\Api\Traits\HasJsonPayload;
use FormGent\Mollie\Api\Types\Method;
/**
 * @see https://docs.mollie.com/reference/v2/mandates-api/create-mandate
 */
class CreateMandateRequest extends ResourceHydratableRequest implements HasPayload, SupportsTestmodeInPayload
{
    use HasJsonPayload;
    /**
     * Define the HTTP method.
     */
    protected static string $method = Method::POST;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = Mandate::class;
    private string $customerId;
    private string $paymentMethod;
    private string $consumerName;
    private ?string $consumerAccount;
    private ?string $consumerBic;
    private ?string $consumerEmail;
    /**
     * @var Date|DateTimeInterface
     */
    private $signatureDate;
    private ?string $mandateReference;
    private ?string $paypalBillingAgreementId;
    public function __construct(string $customerId, string $method, string $consumerName, ?string $consumerAccount = null, ?string $consumerBic = null, ?string $consumerEmail = null, $signatureDate = null, ?string $mandateReference = null, ?string $paypalBillingAgreementId = null)
    {
        $this->customerId = $customerId;
        $this->paymentMethod = $method;
        $this->consumerName = $consumerName;
        $this->consumerAccount = $consumerAccount;
        $this->consumerBic = $consumerBic;
        $this->consumerEmail = $consumerEmail;
        $this->signatureDate = $signatureDate;
        $this->mandateReference = $mandateReference;
        $this->paypalBillingAgreementId = $paypalBillingAgreementId;
    }
    protected function defaultPayload() : array
    {
        return ['method' => $this->paymentMethod, 'consumerName' => $this->consumerName, 'consumerAccount' => $this->consumerAccount, 'consumerBic' => $this->consumerBic, 'consumerEmail' => $this->consumerEmail, 'signatureDate' => $this->signatureDate, 'mandateReference' => $this->mandateReference, 'paypalBillingAgreementId' => $this->paypalBillingAgreementId];
    }
    public function resolveResourcePath() : string
    {
        return "customers/{$this->customerId}/mandates";
    }
}
