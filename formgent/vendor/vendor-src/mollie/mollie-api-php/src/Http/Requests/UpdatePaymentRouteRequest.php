<?php

namespace FormGent\Mollie\Api\Http\Requests;

use DateTimeInterface;
use FormGent\Mollie\Api\Contracts\HasPayload;
use FormGent\Mollie\Api\Contracts\SupportsTestmodeInPayload;
use FormGent\Mollie\Api\Http\Data\Date;
use FormGent\Mollie\Api\Resources\Route;
use FormGent\Mollie\Api\Traits\HasJsonPayload;
use FormGent\Mollie\Api\Types\Method;
class UpdatePaymentRouteRequest extends ResourceHydratableRequest implements HasPayload, SupportsTestmodeInPayload
{
    use HasJsonPayload;
    /**
     * The HTTP method.
     */
    protected static string $method = Method::PATCH;
    /**
     * The resource class the request should be casted to.
     */
    protected $hydratableResource = Route::class;
    private string $paymentId;
    private string $routeId;
    /**
     * This attribute is intentionally not typed because of legacy support.
     *
     * @var DateTimeInterface|Date
     */
    private $releaseDate;
    /**
     * @param  DateTimeInterface|Date  $releaseDate
     */
    public function __construct(string $paymentId, string $routeId, $releaseDate)
    {
        $this->paymentId = $paymentId;
        $this->routeId = $routeId;
        $this->releaseDate = $releaseDate;
    }
    protected function defaultPayload() : array
    {
        return ['releaseDate' => $this->releaseDate];
    }
    public function resolveResourcePath() : string
    {
        return "payments/{$this->paymentId}/routes/{$this->routeId}";
    }
}
