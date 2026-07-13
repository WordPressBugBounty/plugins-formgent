<?php

namespace FormGent\Mollie\Api\Resources;

use FormGent\Mollie\Api\Config;
use FormGent\Mollie\Api\Contracts\IsIteratable;
use FormGent\Mollie\Api\Contracts\IsWrapper;
use FormGent\Mollie\Api\Http\Request;
use FormGent\Mollie\Api\Http\Requests\ResourceHydratableRequest;
use FormGent\Mollie\Api\Http\Response;
use FormGent\Mollie\Api\Utils\Str;
class ResourceResolver
{
    private ResourceHydrator $hydrator;
    public function __construct(ResourceHydrator $hydrator)
    {
        $this->hydrator = $hydrator;
    }
    /**
     * Resolve a response into the appropriate resource type.
     *
     * @return Response|BaseResource|BaseCollection|LazyCollection|IsWrapper
     */
    public function resolve(ResourceHydratableRequest $request, Response $response)
    {
        $targetResourceClass = $request->getHydratableResource();
        if ($targetResourceClass instanceof WrapperResource) {
            $response = $this->resolve($request->resetHydratableResource(), $response);
            return ResourceFactory::createDecoratedResource($response, $targetResourceClass->getWrapper());
        }
        if ($this->isCollectionTarget($targetResourceClass)) {
            $collection = $this->resolveCollection($response, $targetResourceClass);
            return $this->unwrapIterator($request, $collection);
        }
        if ($this->isResourceTarget($targetResourceClass)) {
            $resource = ResourceFactory::create($response->getConnector(), $targetResourceClass);
            return $this->hydrator->hydrate($resource, $response->json(), $response);
        }
        return $response;
    }
    /**
     * @param Response $response
     * @param class-string<ResourceCollection> $targetCollectionClass
     * @return BaseCollection
     */
    private function resolveCollection(Response $response, string $targetCollectionClass) : BaseCollection
    {
        $result = $response->json();
        $collection = ResourceFactory::createCollection($response->getConnector(), $targetCollectionClass);
        $kebabCollectionKey = Config::resourceRegistry()->pluralOf($targetCollectionClass::getResourceClass());
        $data = isset($result->_embedded->{$kebabCollectionKey}) ? $result->_embedded->{$kebabCollectionKey} : $result->_embedded->{Str::snake($kebabCollectionKey)};
        return $this->hydrator->hydrateCollection($collection, $data, $response, $result->_links);
    }
    private function unwrapIterator(Request $request, BaseCollection $collection)
    {
        if ($request instanceof IsIteratable && $request->iteratorEnabled()) {
            /** @var CursorCollection $collection */
            return $collection->getAutoIterator($request->iteratesBackwards());
        }
        return $collection;
    }
    private function isCollectionTarget(string $targetResourceClass) : bool
    {
        return \is_subclass_of($targetResourceClass, BaseCollection::class);
    }
    private function isResourceTarget(string $targetResourceClass) : bool
    {
        return \is_subclass_of($targetResourceClass, BaseResource::class);
    }
}
