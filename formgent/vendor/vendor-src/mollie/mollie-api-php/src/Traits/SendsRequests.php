<?php

namespace FormGent\Mollie\Api\Traits;

use FormGent\Mollie\Api\Contracts\RetryStrategyContract;
use FormGent\Mollie\Api\Exceptions\LogicException;
use FormGent\Mollie\Api\Exceptions\MollieException;
use FormGent\Mollie\Api\Exceptions\RetryableNetworkRequestException;
use FormGent\Mollie\Api\Http\LinearRetryStrategy;
use FormGent\Mollie\Api\Http\PendingRequest;
use FormGent\Mollie\Api\Http\Request;
use FormGent\Mollie\Api\MollieApiClient;
use FormGent\Mollie\Api\Utils\DataTransformer;
/**
 * @mixin MollieApiClient
 */
trait SendsRequests
{
    /**
     * Strategy that defines retry behavior.
     */
    protected RetryStrategyContract $retryStrategy;
    protected function initializeSendsRequests() : void
    {
        $this->retryStrategy = $this->retryStrategy ?? new LinearRetryStrategy();
    }
    /**
     * Set a custom retry strategy implementation.
     */
    public function setRetryStrategy(RetryStrategyContract $strategy) : self
    {
        $this->retryStrategy = $strategy;
        return $this;
    }
    /**
     * @return mixed
     */
    public function send(Request $request)
    {
        $pendingRequest = new PendingRequest($this, $request);
        $pendingRequest = $pendingRequest->executeRequestHandlers();
        $pendingRequest = (new DataTransformer())->transform($pendingRequest);
        $lastException = null;
        for ($attempt = 0; $attempt <= $this->retryStrategy->maxRetries(); $attempt++) {
            if ($attempt > 0) {
                $delayMs = $this->retryStrategy->delayBeforeAttemptMs($attempt);
                \usleep($delayMs * 1000);
            }
            try {
                $response = $this->httpClient->sendRequest($pendingRequest);
                return $pendingRequest->executeResponseHandlers($response);
            } catch (RetryableNetworkRequestException $e) {
                $lastException = $e;
            } catch (MollieException $exception) {
                $exception = $pendingRequest->executeFatalHandlers($exception);
                throw $exception;
            }
        }
        if ($lastException instanceof MollieException) {
            $lastException = $pendingRequest->executeFatalHandlers($lastException);
            throw $lastException;
        }
        // This should be unreachable, but keep a safe fallback for static analysis
        throw new LogicException('Request failed after retries without a final exception.');
    }
}
