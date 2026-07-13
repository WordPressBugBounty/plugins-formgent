<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\IsIteratable;
use FormGent\Mollie\Api\Contracts\SupportsTestmodeInQuery;
use FormGent\Mollie\Api\Resources\RefundCollection;
use FormGent\Mollie\Api\Traits\IsIteratableRequest;
use FormGent\Mollie\Api\Types\PaymentIncludesQuery;
class GetPaginatedRefundsRequest extends PaginatedRequest implements IsIteratable, SupportsTestmodeInQuery
{
    use IsIteratableRequest;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = RefundCollection::class;
    public function __construct(?string $from = null, ?int $limit = null, bool $embedPayment = \false, ?string $profileId = null)
    {
        parent::__construct($from, $limit);
        $this->query()->add('embed', $embedPayment ? PaymentIncludesQuery::PAYMENT : null)->add('profileId', $profileId);
    }
    public function resolveResourcePath() : string
    {
        return 'refunds';
    }
}
