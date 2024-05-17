<?php
namespace Apie\Common;

use Apie\Common\Interfaces\ApieFacadeInterface;
use Apie\Common\Interfaces\RouteDefinitionProviderInterface;
use Apie\Common\RouteDefinitions\ActionHashmap;
use Apie\Core\Actions\ActionInterface;
use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Core\Datalayers\ApieDatalayer;
use Apie\Core\Datalayers\Lists\EntityListInterface;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Identifiers\IdentifierInterface;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Serializer\Serializer;
use LogicException;
use ReflectionClass;
use ReflectionMethod;

final class ApieFacade implements ApieFacadeInterface
{
    public function __construct(
        private RouteDefinitionProviderInterface $routeDefinitionProvider,
        private BoundedContextHashmap $boundedContextHashmap,
        private Serializer $serializer,
        private ApieDatalayer $apieDatalayer
    ) {
    }

    private function getBoundedContext(BoundedContext|BoundedContextId $boundedContext): BoundedContext
    {
        if ($boundedContext instanceof BoundedContext) {
            return $boundedContext;
        }
        return $this->boundedContextHashmap[$boundedContext->toNative()];
    }

    /**
     * @template T of EntityInterface
     * @param class-string<T>|ReflectionClass<T> $class
     * @return EntityListInterface<T>
     */
    public function all(string|ReflectionClass $class, BoundedContext|BoundedContextId $boundedContext): EntityListInterface
    {
        if (is_string($class)) {
            $class = new ReflectionClass($class);
        }

        return $this->apieDatalayer->all($class, $this->getBoundedContext($boundedContext));
    }

    /**
     * @template T of EntityInterface
     * @param IdentifierInterface<T> $identifier
     * @return T
     */
    public function find(IdentifierInterface $identifier, BoundedContext|BoundedContextId $boundedContext): EntityInterface
    {
        return $this->apieDatalayer->find($identifier, $this->getBoundedContext($boundedContext));
    }

    public function removeExisting(EntityInterface $entity, BoundedContext|BoundedContextId $boundedContext): void
    {
        $this->apieDatalayer->removeExisting($entity, $this->getBoundedContext($boundedContext));
    }

    /**
     * @template T of EntityInterface
     * @param T $entity
     * @return T
     */
    public function persistNew(EntityInterface $entity, BoundedContext|BoundedContextId $boundedContext): EntityInterface
    {
        return $this->apieDatalayer->persistNew($entity, $this->getBoundedContext($boundedContext));
    }

    /**
     * @template T of EntityInterface
     * @param T $entity
     * @return T
     */
    public function persistExisting(EntityInterface $entity, BoundedContext|BoundedContextId $boundedContext): EntityInterface
    {
        return $this->apieDatalayer->persistExisting($entity, $this->getBoundedContext($boundedContext));
    }

    public function normalize(mixed $object, ApieContext $apieContext): string|int|float|bool|ItemList|ItemHashmap|null
    {
        return $this->serializer->normalize($object, $apieContext);
    }

    public function denormalizeNewObject(string|int|float|bool|ItemList|ItemHashmap|array|null $object, string $desiredType, ApieContext $apieContext): mixed
    {
        return $this->serializer->denormalizeNewObject($object, $desiredType, $apieContext);
    }

    public function denormalizeOnExistingObject(ItemHashmap $object, object $existingObject, ApieContext $apieContext): mixed
    {
        return $this->serializer->denormalizeOnExistingObject($object, $existingObject, $apieContext);
    }

    public function denormalizeOnMethodCall(string|int|float|bool|ItemList|ItemHashmap|array|null $input, ?object $object, ReflectionMethod $method, ApieContext $apieContext): mixed
    {
        return $this->serializer->denormalizeOnMethodCall($input, $object, $method, $apieContext);
    }

    /**
     * @deprecated use createAction instead
     */
    public function getAction(string $boundedContextId, string $operationId, ApieContext $apieContext): ActionInterface
    {
        $actions = $this->getActionsForBoundedContext(new BoundedContextId($boundedContextId), $apieContext);
        foreach ($actions as $action) {
            if ($action->getOperationId() === $operationId) {
                return new($action->getAction())($this);
            }
        }
        throw new LogicException(sprintf('"%s" operation id not found!', $operationId));
    }

    public function createAction(ApieContext $apieContext): ActionInterface
    {
        if ($apieContext->hasContext(ContextConstants::APIE_ACTION)) {
            return new ($apieContext->getContext(ContextConstants::APIE_ACTION))($this);
        }

        return $this->getAction(
            $apieContext->getContext(ContextConstants::BOUNDED_CONTEXT_ID),
            $apieContext->getContext(ContextConstants::OPERATION_ID),
            $apieContext,
        );
    }

    public function getActionsForBoundedContext(BoundedContextId|BoundedContext $boundedContext, ApieContext $apieContext): ActionHashmap
    {
        if ($boundedContext instanceof BoundedContextId) {
            $boundedContext = $this->boundedContextHashmap[$boundedContext->toNative()];
        }

        return $this->routeDefinitionProvider->getActionsForBoundedContext($boundedContext, $apieContext);
    }
}
