<?php
namespace Apie\Common;

use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\Datalayers\ApieDatalayer;
use Apie\Core\Datalayers\Lists\LazyLoadedList;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Identifiers\IdentifierInterface;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\RouteDefinitions\ActionHashmap;
use Apie\Core\RouteDefinitions\RouteDefinitionProviderInterface;
use Apie\Serializer\Serializer;
use LogicException;
use ReflectionClass;
use ReflectionMethod;

final class ApieFacade
{
    public function __construct(
        private RouteDefinitionProviderInterface $routeDefinitionProvider,
        private BoundedContextHashmap $boundedContextHashmap,
        private Serializer $serializer,
        private ApieDatalayer $apieDatalayer
    ) {
    }

    /**
     * @template T of EntityInterface
     * @param class-string<T>|ReflectionClass<T> $class
     * @return LazyLoadedList<T>
     */
    public function all(string|ReflectionClass $class): LazyLoadedList
    {
        if (is_string($class)) {
            $class = new ReflectionClass($class);
        }

        return $this->apieDatalayer->all($class);
    }

    /**
     * @template T of EntityInterface
     * @param IdentifierInterface<T> $identifier
     * @return T
     */
    public function find(IdentifierInterface $identifier): EntityInterface
    {
        return $this->apieDatalayer->find($identifier);
    }

    /**
     * @template T of EntityInterface
     * @param T $entity
     * @return T
     */
    public function persistNew(EntityInterface $entity): EntityInterface
    {
        return $this->apieDatalayer->persistNew($entity);
    }

    /**
     * @template T of EntityInterface
     * @param T $entity
     * @return T
     */
    public function persistExisting(EntityInterface $entity): EntityInterface
    {
        return $this->apieDatalayer->persistExisting($entity);
    }

    public function normalize(mixed $object, ApieContext $apieContext): string|int|float|bool|ItemList|ItemHashmap|null
    {
        return $this->serializer->normalize($object, $apieContext);
    }

    public function denormalizeNewObject(string|int|float|bool|ItemList|ItemHashmap|array|null $object, string $desiredType, ApieContext $apieContext): mixed
    {
        return $this->serializer->denormalizeNewObject($object, $desiredType, $apieContext);
    }

    public function denormalizeOnMethodCall(string|int|float|bool|ItemList|ItemHashmap|array|null $input, ?object $object, ReflectionMethod $method, ApieContext $apieContext): mixed
    {
        return $this->serializer->denormalizeOnMethodCall($input, $object, $method, $apieContext);
    }

    public function getAction(string $boundedContextId, string $operationId, ApieContext $apieContext): ApieFacadeAction
    {
        $actions = $this->getActionsForBoundedContext(new BoundedContextId($boundedContextId), $apieContext);
        foreach ($actions as $action) {
            if ($action->getOperationId() === $operationId) {
                return $this->createAction($action->getAction());
            }
        }
        throw new LogicException(sprintf('"%s" operation id not found!', $operationId));
    }

    /**
     * @param class-string<ApieFacadeAction> $classAction
     */
    private function createAction(string $classAction): ApieFacadeAction
    {
        return new $classAction($this);
    }
    

    public function getActionsForBoundedContext(BoundedContextId|BoundedContext $boundedContext, ApieContext $apieContext): ActionHashmap
    {
        if ($boundedContext instanceof BoundedContextId) {
            $boundedContext = $this->boundedContextHashmap[$boundedContext->toNative()];
        }

        return $this->routeDefinitionProvider->getActionsForBoundedContext($boundedContext, $apieContext);
    }
}
