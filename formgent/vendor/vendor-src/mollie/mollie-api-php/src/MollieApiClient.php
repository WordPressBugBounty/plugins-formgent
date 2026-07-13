<?php

namespace FormGent\Mollie\Api;

use FormGent\Mollie\Api\Contracts\Connector;
use FormGent\Mollie\Api\Contracts\HttpAdapterContract;
use FormGent\Mollie\Api\Contracts\IdempotencyKeyGeneratorContract;
use FormGent\Mollie\Api\Contracts\MollieHttpAdapterPickerContract;
use FormGent\Mollie\Api\EndpointCollection\BalanceEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\BalanceReportEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\BalanceTransactionEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\CapabilityEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\ChargebackEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\ClientEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\ClientLinkEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\ConnectBalanceTransferEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\CustomerEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\CustomerPaymentsEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\InvoiceEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\MandateEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\MethodEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\MethodIssuerEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\OnboardingEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\OrganizationEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\OrganizationPartnerEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\PaymentCaptureEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\PaymentChargebackEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\PaymentEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\PaymentLinkEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\PaymentLinkPaymentEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\PaymentRefundEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\PaymentRouteEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\PayoutEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\PermissionEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\ProfileEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\ProfileMethodEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\RefundEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\SalesInvoiceEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\SessionEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\SettlementCaptureEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\SettlementChargebackEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\SettlementEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\SettlementPaymentEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\SettlementRefundEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\SubscriptionEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\SubscriptionPaymentEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\TerminalEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\TerminalPairingCodeEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\WalletEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\WebhookEndpointCollection;
use FormGent\Mollie\Api\EndpointCollection\WebhookEventEndpointCollection;
use FormGent\Mollie\Api\Fake\MockMollieClient;
use FormGent\Mollie\Api\Http\Adapter\MollieHttpAdapterPicker;
use FormGent\Mollie\Api\Idempotency\DefaultIdempotencyKeyGenerator;
use FormGent\Mollie\Api\Traits\HandlesAuthentication;
use FormGent\Mollie\Api\Traits\HandlesDebugging;
use FormGent\Mollie\Api\Traits\HandlesIdempotency;
use FormGent\Mollie\Api\Traits\HandlesTestmode;
use FormGent\Mollie\Api\Traits\HandlesVersions;
use FormGent\Mollie\Api\Traits\HasEndpoints;
use FormGent\Mollie\Api\Traits\HasMiddleware;
use FormGent\Mollie\Api\Traits\HasRequestProperties;
use FormGent\Mollie\Api\Traits\Initializable;
use FormGent\Mollie\Api\Traits\SendsRequests;
use FormGent\Mollie\Api\Utils\Url;
/**
 * Main Mollie API Client
 *
 * Access endpoint collections via magic properties:
 * @example $client->payments->get('tr_xxx')
 * @example $client->customers->create(['name' => 'John Doe'])
 *
 * @property BalanceEndpointCollection $balances
 * @property BalanceReportEndpointCollection $balanceReports
 * @property BalanceTransactionEndpointCollection $balanceTransactions
 * @property ChargebackEndpointCollection $chargebacks
 * @property CapabilityEndpointCollection $capabilities
 * @property ClientEndpointCollection $clients
 * @property ClientLinkEndpointCollection $clientLinks
 * @property ConnectBalanceTransferEndpointCollection $connectBalanceTransfers
 * @property CustomerPaymentsEndpointCollection $customerPayments
 * @property CustomerEndpointCollection $customers
 * @property InvoiceEndpointCollection $invoices
 * @property MandateEndpointCollection $mandates
 * @property MethodEndpointCollection $methods
 * @property MethodIssuerEndpointCollection $methodIssuers
 * @property OnboardingEndpointCollection $onboarding
 * @property OrganizationEndpointCollection $organizations
 * @property OrganizationPartnerEndpointCollection $organizationPartners
 * @property PaymentEndpointCollection $payments
 * @property PaymentCaptureEndpointCollection $paymentCaptures
 * @property PaymentChargebackEndpointCollection $paymentChargebacks
 * @property PaymentLinkEndpointCollection $paymentLinks
 * @property PaymentLinkPaymentEndpointCollection $paymentLinkPayments
 * @property PaymentRefundEndpointCollection $paymentRefunds
 * @property PaymentRouteEndpointCollection $paymentRoutes
 * @property PermissionEndpointCollection $permissions
 * @property PayoutEndpointCollection $payouts
 * @property ProfileEndpointCollection $profiles
 * @property ProfileMethodEndpointCollection $profileMethods
 * @property RefundEndpointCollection $refunds
 * @property SalesInvoiceEndpointCollection $salesInvoices
 * @property SessionEndpointCollection $sessions
 * @property SettlementCaptureEndpointCollection $settlementCaptures
 * @property SettlementChargebackEndpointCollection $settlementChargebacks
 * @property SettlementEndpointCollection $settlements
 * @property SettlementPaymentEndpointCollection $settlementPayments
 * @property SettlementRefundEndpointCollection $settlementRefunds
 * @property SubscriptionEndpointCollection $subscriptions
 * @property SubscriptionPaymentEndpointCollection $subscriptionPayments
 * @property TerminalEndpointCollection $terminals
 * @property TerminalPairingCodeEndpointCollection $terminalPairingCodes
 * @property WalletEndpointCollection $wallets
 * @property WebhookEndpointCollection $webhooks
 * @property WebhookEventEndpointCollection $webhookEvents
 * @property HttpAdapterContract $httpClient
 */
class MollieApiClient implements Connector
{
    use HandlesAuthentication;
    use HandlesDebugging;
    use HandlesIdempotency;
    use HandlesTestmode;
    use HandlesVersions;
    use HasEndpoints;
    use HasMiddleware;
    use HasRequestProperties;
    use Initializable;
    use SendsRequests;
    /**
     * Version of our client.
     */
    public const CLIENT_VERSION = '3.13.1';
    /**
     * Endpoint of the remote API.
     */
    public const API_ENDPOINT = 'https://api.mollie.com';
    /**
     * Version of the remote API.
     */
    public const API_VERSION = 'v2';
    /**
     * Http client used to perform requests.
     */
    protected HttpAdapterContract $httpClient;
    /**
     * @param  \GuzzleHttp\ClientInterface|\Mollie\Api\Contracts\HttpAdapterContract|null  $client
     *
     * @throws \Mollie\Api\Exceptions\IncompatiblePlatformException|\Mollie\Api\Exceptions\UnrecognizedClientException
     */
    public function __construct($client = null, ?MollieHttpAdapterPickerContract $adapterPicker = null, ?IdempotencyKeyGeneratorContract $idempotencyKeyGenerator = null)
    {
        $adapterPicker = $adapterPicker ?: new MollieHttpAdapterPicker();
        $this->httpClient = $adapterPicker->pickHttpAdapter($client);
        CompatibilityChecker::make()->checkCompatibility();
        $this->idempotencyKeyGenerator = $idempotencyKeyGenerator ?? new DefaultIdempotencyKeyGenerator();
        $this->initializeTraits();
    }
    protected function defaultHeaders() : array
    {
        return ['X-Mollie-Client-Info' => \php_uname(), 'Accept' => 'application/json'];
    }
    public function getHttpClient() : HttpAdapterContract
    {
        return $this->httpClient;
    }
    public function resolveBaseUrl() : string
    {
        return Url::join($this->apiEndpoint, self::API_VERSION);
    }
    public static function fake(array $expectedResponses = [], bool $retainRequests = \false) : MockMollieClient
    {
        return new MockMollieClient($expectedResponses, $retainRequests);
    }
    public function __serialize() : array
    {
        return ['apiEndpoint' => $this->apiEndpoint, 'httpClient' => $this->httpClient, 'idempotencyKeyGenerator' => $this->idempotencyKeyGenerator, 'testmode' => $this->testmode, 'versionStrings' => $this->versionStrings];
    }
    public function __unserialize(array $data) : void
    {
        $this->apiEndpoint = $data['apiEndpoint'];
        $this->httpClient = $data['httpClient'];
        $this->idempotencyKeyGenerator = $data['idempotencyKeyGenerator'];
        $this->testmode = $data['testmode'];
        $this->versionStrings = $data['versionStrings'];
    }
}
