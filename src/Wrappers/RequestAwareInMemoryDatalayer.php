<?php
namespace Apie\Common\Wrappers;

use Apie\Common\Interfaces\BoundedContextSelection;
use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Datalayers\ApieDatalayerWithFilters;
use Apie\Core\Datalayers\ApieDatalayerWithSupport;
use Apie\Core\Datalayers\BoundedContextAwareApieDatalayer;
use Apie\Core\Datalayers\Concerns\FiltersOnAllFields;
use Apie\Core\Datalayers\InMemory\InMemoryDatalayer;
use Apie\Core\Datalayers\Lists\EntityListInterface;
use Apie\Core\Datalayers\Search\LazyLoadedListFilterer;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Identifiers\IdentifierInterface;
use ReflectionClass;

final class RequestAwareInMemoryDatalayer implements ApieDatalayerWithFilters, BoundedContextAwareApieDatalayer, ApieDatalayerWithSupport
{
    use FiltersOnAllFields;

    /**
     * @var array<string, InMemoryDatalayer>
     */
    private array $createdRepositories = [];

    public function __construct(
        private readonly BoundedContextSelection $boundedContextSelected,
        private readonly LazyLoadedListFilterer $filterer
    ) {
    }

    public function isSupported(
        EntityInterface|ReflectionClass|IdentifierInterface $instance,
        BoundedContextId $boundedContextId
    ): bool {
        $className = ($instance instanceof ReflectionClass) ? $instance->name : get_class($instance);
        if ($instance instanceof IdentifierInterface) {
            $className = $instance->getReferenceFor()->name;
        }
        $boundedContext = $this->boundedContextSelected->getBoundedContextFromClassName($className);
        return $boundedContext ? $boundedContext->getId()->toNative() === $boundedContextId->toNative() : false;
    }

    public function all(ReflectionClass $class, ?BoundedContext $boundedContext = null): EntityListInterface
    {
        return $this->getRepository($class, $boundedContext)->all($class, $boundedContext);
    }

    public function find(IdentifierInterface $identifier, ?BoundedContext $boundedContext = null): EntityInterface
    {
        return $this->getRepository($identifier::getReferenceFor(), $boundedContext)->find($identifier, $boundedContext);
    }

    /**
     * @template T of EntityInterface
     * @param T $entity
     * @return T
     */
    public function persistNew(EntityInterface $entity, ?BoundedContext $boundedContext = null): EntityInterface
    {
        return $this->getRepository($entity->getId()::getReferenceFor(), $boundedContext)->persistNew($entity, $boundedContext);
    }

    /**
     * @template T of EntityInterface
     * @param T $entity
     * @return T
     */
    public function persistExisting(EntityInterface $entity, ?BoundedContext $boundedContext = null): EntityInterface
    {
        return $this->getRepository($entity->getId()::getReferenceFor(), $boundedContext)->persistExisting($entity, $boundedContext);
    }

    /**
     * @param ReflectionClass<object> $class
     */
    private function getRepository(ReflectionClass $class, ?BoundedContext $boundedContext = null): InMemoryDatalayer
    {
        if ($boundedContext === null) {
            $boundedContext = $this->boundedContextSelected->getBoundedContextFromRequest();
            if (!$boundedContext) {
                $boundedContext = $this->boundedContextSelected->getBoundedContextFromClassName($class->name);
            }
        }
        $boundedContextId = $boundedContext ? $boundedContext->getId() : new BoundedContextId('unknown');
        if (!isset($this->createdRepositories[$boundedContextId->toNative()])) {
            $this->createdRepositories[$boundedContextId->toNative()] = new InMemoryDatalayer($boundedContextId, $this->filterer);
        }

        return $this->createdRepositories[$boundedContextId->toNative()];
    }

    public function removeExisting(EntityInterface $entity, ?BoundedContext $boundedContext = null): void
    {
        $this->getRepository($entity->getId()::getReferenceFor(), $boundedContext)->removeExisting($entity, $boundedContext);
    }
}
