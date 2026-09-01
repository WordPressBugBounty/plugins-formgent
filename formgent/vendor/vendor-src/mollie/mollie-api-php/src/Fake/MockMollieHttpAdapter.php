<?php

namespace FormGent\Mollie\Api\Fake;

use Closure;
use FormGent\Mollie\Api\Contracts\HttpAdapterContract;
use FormGent\Mollie\Api\Exceptions\RetryableNetworkRequestException;
use FormGent\Mollie\Api\Http\PendingRequest;
use FormGent\Mollie\Api\Http\Response;
use FormGent\Mollie\Api\Traits\HasDefaultFactories;
use FormGent\Mollie\Api\Utils\Arr;
use PHPUnit\Framework\Assert as PHPUnit;
use FormGent\Psr\Http\Client\NetworkExceptionInterface;
use FormGent\Psr\Http\Client\RequestExceptionInterface;
class MockMollieHttpAdapter implements HttpAdapterContract
{
    use HasDefaultFactories;
    /**
     * @var array<string, MockResponse|Closure(PendingRequest): MockResponse>
     */
    private array $expected;
    private bool $retainRequests;
    private array $recorded = [];
    public function __construct(array $expectedResponses = [], bool $retainRequests = \false)
    {
        $this->expected = $expectedResponses;
        $this->retainRequests = $retainRequests;
    }
    /**
     * {@inheritDoc}
     */
    public function sendRequest(PendingRequest $pendingRequest) : Response
    {
        $requestClass = \get_class($pendingRequest->getRequest());
        $this->guardAgainstStrayRequests($requestClass);
        try {
            $mockedResponse = $this->getResponse($requestClass, $pendingRequest);
        } catch (NetworkExceptionInterface $e) {
            throw new RetryableNetworkRequestException($pendingRequest, $e->getMessage());
        } catch (RequestExceptionInterface $e) {
            throw new RetryableNetworkRequestException($pendingRequest, $e->getMessage());
        }
        $response = new Response($mockedResponse->createPsrResponse(), $pendingRequest->createPsrRequest(), $pendingRequest);
        $this->recorded[] = [$pendingRequest, $response];
        return $response;
    }
    private function guardAgainstStrayRequests(string $requestClass) : void
    {
        if (!Arr::has($this->expected, $requestClass)) {
            throw new \RuntimeException('The request class ' . $requestClass . ' is not expected.');
        }
    }
    /**
     * Get the mocked response and remove it from the expected responses.
     */
    private function getResponse(string $requestClass, PendingRequest $pendingRequest) : MockResponse
    {
        $mockedResponse = Arr::get($this->expected, $requestClass);
        if ($mockedResponse instanceof Closure) {
            $this->forgetRequest($requestClass);
            return $mockedResponse($pendingRequest);
        }
        if (!$mockedResponse instanceof SequenceMockResponse) {
            $this->forgetRequest($requestClass);
            return $mockedResponse;
        }
        $response = $mockedResponse->shift();
        if ($mockedResponse->isEmpty()) {
            $this->forgetRequest($requestClass);
        }
        if ($response instanceof Closure) {
            $response = $response($pendingRequest);
        }
        return $response;
    }
    public function recorded(?callable $callback = null) : array
    {
        if ($callback === null) {
            return $this->recorded;
        }
        return \array_filter($this->recorded, fn($recorded) => \call_user_func_array($callback, $recorded));
    }
    private function forgetRequest(string $requestClass) : void
    {
        if (!$this->retainRequests) {
            Arr::forget($this->expected, $requestClass);
        }
    }
    /**
     * @param  string|callable  $callback
     */
    public function assertSent($callback) : void
    {
        if (\is_string($callback)) {
            $callback = fn(PendingRequest $request) => \get_class($request->getRequest()) === $callback;
        }
        PHPUnit::assertTrue(\count($this->recorded($callback)) > 0, 'No requests were sent.');
    }
    public function assertSentCount(int $count) : void
    {
        PHPUnit::assertEquals($count, \count($this->recorded), 'The expected number of requests was not sent.');
    }
    /**
     * {@inheritDoc}
     */
    public function version() : string
    {
        return 'mock-client/2.0';
    }
}
