<?php
namespace Apie\Common\Wrappers;

use Apie\Common\Interfaces\BoundedContextSelection;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Datalayers\ApieDatalayerWithFilters;
use Apie\Core\Datalayers\ApieDatalayerWithSupport;
use Apie\Core\Datalayers\Concerns\FiltersOnAllFields;
use Apie\Core\Datalayers\InMemory\InMemoryDatalayer;
use Apie\Core\Datalayers\Lists\EntityListInterface;
use Apie\Core\Datalayers\Search\LazyLoadedListFilterer;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Identifiers\IdentifierInterface;
use ReflectionClass;

final class RequestAwareInMemoryDatalayer implements ApieDatalayerWithFilters, ApieDatalayerWithSupport
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

    public function all(ReflectionClass $class, ?BoundedContextId $boundedContextId = null): EntityListInterface
    {
        return $this->getRepository($class, $boundedContextId)
            ->all($class, $boundedContextId);
    }

    public function find(IdentifierInterface $identifier, ?BoundedContextId $boundedContextId = null): EntityInterface
    {
        return $this->getRepository($identifier::getReferenceFor(), $boundedContextId)
            ->find($identifier, $boundedContextId);
    }

    /**
     * @template T of EntityInterface
     * @param T $entity
     * @return T
     */
    public function persistNew(EntityInterface $entity, ?BoundedContextId $boundedContextId = null): EntityInterface
    {
        return $this->getRepository($entity->getId()::getReferenceFor(), $boundedContextId)
            ->persistNew($entity, $boundedContextId);
    }

    /**
     * @template T of EntityInterface
     * @param T $entity
     * @return T
     */
    public function persistExisting(EntityInterface $entity, ?BoundedContextId $boundedContextId = null): EntityInterface
    {
        return $this->getRepository($entity->getId()::getReferenceFor(), $boundedContextId)
            ->persistExisting($entity, $boundedContextId);
    }

    /**
     * @param ReflectionClass<object> $class
     */
    private function getRepository(ReflectionClass $class, ?BoundedContextId $boundedContextId = null): InMemoryDatalayer
    {
        if ($boundedContextId === null) {
            $boundedContextId = $this->boundedContextSelected->getBoundedContextFromRequest()?->getId();
            if (!$boundedContextId) {
                $boundedContextId = $this->boundedContextSelected->getBoundedContextFromClassName($class->name)?->getId();
            }
        }
        $boundedContextId ??= new BoundedContextId('unknown');
        if (!isset($this->createdRepositories[$boundedContextId->toNative()])) {
            $this->createdRepositories[$boundedContextId->toNative()] = new InMemoryDatalayer($boundedContextId, $this->filterer);
        }

        return $this->createdRepositories[$boundedContextId->toNative()];
    }

    public function removeExisting(EntityInterface $entity, ?BoundedContextId $boundedContextId = null): void
    {
        $this->getRepository($entity->getId()::getReferenceFor(), $boundedContextId)
            ->removeExisting($entity, $boundedContextId);
    }
}
