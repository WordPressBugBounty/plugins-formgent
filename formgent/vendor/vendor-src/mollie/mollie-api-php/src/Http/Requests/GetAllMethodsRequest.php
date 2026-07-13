<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Http\Data\Money;
use FormGent\Mollie\Api\Resources\MethodCollection;
use FormGent\Mollie\Api\Types\Method as HttpMethod;
use FormGent\Mollie\Api\Types\MethodQuery;
use FormGent\Mollie\Api\Utils\Arr;
/**
 * @see https://docs.mollie.com/reference/list-all-methods
 */
class GetAllMethodsRequest extends ResourceHydratableRequest
{
    /**
     * Define the HTTP method.
     */
    protected static string $method = HttpMethod::GET;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = MethodCollection::class;
    private bool $includeIssuers;
    private bool $includePricing;
    private ?string $locale;
    private ?Money $amount;
    private ?string $profileId;
    public function __construct(bool $includeIssuers = \false, bool $includePricing = \false, ?string $locale = null, ?Money $amount = null, ?string $profileId = null)
    {
        $this->includeIssuers = $includeIssuers;
        $this->includePricing = $includePricing;
        $this->locale = $locale;
        $this->amount = $amount;
        $this->profileId = $profileId;
    }
    protected function defaultQuery() : array
    {
        return ['include' => Arr::join([$this->includeIssuers ? MethodQuery::INCLUDE_ISSUERS : null, $this->includePricing ? MethodQuery::INCLUDE_PRICING : null]), 'locale' => $this->locale, 'amount' => $this->amount, 'profileId' => $this->profileId];
    }
    public function resolveResourcePath() : string
    {
        return 'methods/all';
    }
}
