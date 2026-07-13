<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\IsIteratable;
use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\ChargebackCollection;
use FormGent\Mollie\Api\Traits\IsIteratableRequest;
use FormGent\Mollie\Api\Types\PaymentIncludesQuery;
class GetPaginatedChargebacksRequest extends PaginatedRequest implements IsIteratable, SupportsTestmodeInQuery
{
    use IsIteratableRequest;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = ChargebackCollection::class;
    public function __construct(?string $from = null, ?int $limit = null, ?bool $includePayment = null, ?string $profileId = null)
    {
        parent::__construct($from, $limit);
        $this->query()->add('embed', $includePayment ? PaymentIncludesQuery::PAYMENT : null)->add('profileId', $profileId);
    }
    public function resolveResourcePath() : string
    {
        return 'chargebacks';
    }
}
