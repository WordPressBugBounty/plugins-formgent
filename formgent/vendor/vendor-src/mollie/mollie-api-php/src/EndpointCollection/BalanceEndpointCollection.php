<?php

namespace FormGent\Mollie\Api\EndpointCollection;

use FormGent\Mollie\Api\Exceptions\RequestException;
use FormGent\Mollie\Api\Factories\SortablePaginatedQueryFactory;
use FormGent\Mollie\Api\Http\Requests\GetBalanceRequest;
use FormGent\Mollie\Api\Http\Requests\GetPaginatedBalanceRequest;
use FormGent\Mollie\Api\Resources\Balance;
use FormGent\Mollie\Api\Resources\BalanceCollection;
use FormGent\Mollie\Api\Resources\LazyCollection;
use FormGent\Mollie\Api\Utils\Utility;
class BalanceEndpointCollection extends EndpointCollection
{
    /**
     * Retrieve a single balance from Mollie.
     *
     * Will throw an ApiException if the balance id is invalid or the resource cannot be found.
     *
     * @param  bool|array  $testmode
     *
     * @throws RequestException
     */
    public function get(string $id, $testmode = \false) : Balance
    {
        $testmode = Utility::extractBool($testmode, 'testmode', \false);
        return $this->send((new GetBalanceRequest($id))->test($testmode));
    }
    /**
     * Retrieve the primary balance from Mollie.
     *
     * Will throw an ApiException if the balance id is invalid or the resource cannot be found.
     *
     * @param  bool|array  $testmode
     *
     * @throws RequestException
     */
    public function primary($testmode = \false) : Balance
    {
        /** @var Balance */
        return $this->get('primary', $testmode);
    }
    /**
     * Get the balance endpoint.
     */
    public function page(?string $from = null, ?int $limit = null, array $filters = []) : BalanceCollection
    {
        $testmode = Utility::extractBool($filters, 'testmode', \false);
        $query = SortablePaginatedQueryFactory::new()->withQuery(['from' => $from, 'limit' => $limit, 'filters' => $filters])->create();
        return $this->send((new GetPaginatedBalanceRequest($query->from, $query->limit, $query->sort))->test($testmode));
    }
    /**
     * Create an iterator for iterating over balances retrieved from Mollie.
     *
     * @param  string  $from  The first Balance ID you want to include in your list.
     * @param  bool  $iterateBackwards  Set to true for reverse order iteration (default is false).
     */
    public function iterator(?string $from = null, ?int $limit = null, array $filters = [], bool $iterateBackwards = \false) : LazyCollection
    {
        $testmode = Utility::extractBool($filters, 'testmode', \false);
        $query = SortablePaginatedQueryFactory::new()->withQuery(['from' => $from, 'limit' => $limit, 'filters' => $filters])->create();
        return $this->send((new GetPaginatedBalanceRequest($query->from, $query->limit, $query->sort))->useIterator()->setIterationDirection($iterateBackwards)->test($testmode));
    }
}
