<?php

namespace FormGent\Mollie\Api\Http\Requests;

use FormGent\Mollie\Api\Contracts\IsIteratable;
use FormGent\Mollie\Api\Resources\ClientCollection;
use FormGent\Mollie\Api\Traits\IsIteratableRequest;
use FormGent\Mollie\Api\Types\ClientQuery;
use FormGent\Mollie\Api\Utils\Arr;
class GetPaginatedClientRequest extends PaginatedRequest implements IsIteratable
{
    use IsIteratableRequest;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = ClientCollection::class;
    public function __construct(?string $from = null, ?int $limit = null, ?bool $embedOrganization = null, ?bool $embedOnboarding = null)
    {
        parent::__construct($from, $limit);
        $this->query()->add('embed', Arr::join([$embedOrganization ? ClientQuery::EMBED_ORGANIZATION : null, $embedOnboarding ? ClientQuery::EMBED_ONBOARDING : null]));
    }
    public function resolveResourcePath() : string
    {
        return 'clients';
    }
}
