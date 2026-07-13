<?php

namespace FormGent\Mollie\Api\Traits;

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
use FormGent\Mollie\Api\MollieApiClient;
/**
 * @mixin MollieApiClient
 */
trait HasEndpoints
{
    protected string $apiEndpoint = MollieApiClient::API_ENDPOINT;
    protected static array $endpoints = [];
    protected function initializeHasEndpoints() : void
    {
        if (!empty(static::$endpoints)) {
            return;
        }
        $endpointClasses = ['balances' => BalanceEndpointCollection::class, 'balanceReports' => BalanceReportEndpointCollection::class, 'balanceTransactions' => BalanceTransactionEndpointCollection::class, 'capabilities' => CapabilityEndpointCollection::class, 'chargebacks' => ChargebackEndpointCollection::class, 'clients' => ClientEndpointCollection::class, 'clientLinks' => ClientLinkEndpointCollection::class, 'connectBalanceTransfers' => ConnectBalanceTransferEndpointCollection::class, 'customerPayments' => CustomerPaymentsEndpointCollection::class, 'customers' => CustomerEndpointCollection::class, 'invoices' => InvoiceEndpointCollection::class, 'mandates' => MandateEndpointCollection::class, 'methods' => MethodEndpointCollection::class, 'methodIssuers' => MethodIssuerEndpointCollection::class, 'onboarding' => OnboardingEndpointCollection::class, 'organizationPartners' => OrganizationPartnerEndpointCollection::class, 'organizations' => OrganizationEndpointCollection::class, 'payments' => PaymentEndpointCollection::class, 'paymentRefunds' => PaymentRefundEndpointCollection::class, 'paymentCaptures' => PaymentCaptureEndpointCollection::class, 'paymentChargebacks' => PaymentChargebackEndpointCollection::class, 'paymentLinks' => PaymentLinkEndpointCollection::class, 'paymentLinkPayments' => PaymentLinkPaymentEndpointCollection::class, 'paymentRoutes' => PaymentRouteEndpointCollection::class, 'permissions' => PermissionEndpointCollection::class, 'payouts' => PayoutEndpointCollection::class, 'profiles' => ProfileEndpointCollection::class, 'profileMethods' => ProfileMethodEndpointCollection::class, 'refunds' => RefundEndpointCollection::class, 'salesInvoices' => SalesInvoiceEndpointCollection::class, 'sessions' => SessionEndpointCollection::class, 'settlementCaptures' => SettlementCaptureEndpointCollection::class, 'settlementChargebacks' => SettlementChargebackEndpointCollection::class, 'settlementPayments' => SettlementPaymentEndpointCollection::class, 'settlementRefunds' => SettlementRefundEndpointCollection::class, 'settlements' => SettlementEndpointCollection::class, 'subscriptions' => SubscriptionEndpointCollection::class, 'subscriptionPayments' => SubscriptionPaymentEndpointCollection::class, 'terminals' => TerminalEndpointCollection::class, 'terminalPairingCodes' => TerminalPairingCodeEndpointCollection::class, 'wallets' => WalletEndpointCollection::class, 'webhooks' => WebhookEndpointCollection::class, 'webhookEvents' => WebhookEventEndpointCollection::class];
        foreach ($endpointClasses as $name => $class) {
            static::$endpoints[$name] = $class;
        }
    }
    /**
     * @param  string  $url
     */
    public function setApiEndpoint($url) : self
    {
        $this->apiEndpoint = \rtrim(\trim($url), '/');
        return $this;
    }
    public function getApiEndpoint() : string
    {
        return $this->apiEndpoint;
    }
    /**
     * Magic getter to access the endpoints.
     *
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function __get(string $name)
    {
        if (isset(static::$endpoints[$name])) {
            return new static::$endpoints[$name]($this);
        }
        throw new \Exception("Undefined endpoint: {$name}");
    }
}
