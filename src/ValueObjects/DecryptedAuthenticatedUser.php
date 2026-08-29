<?php
namespace Apie\Common\ValueObjects;

use Apie\Core\Attributes\FakeMethod;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Identifiers\IdentifierInterface;
use Apie\Core\ValueObjects\Exceptions\InvalidStringForValueObjectException;
use Apie\Core\ValueObjects\Interfaces\StringValueObjectInterface;
use Apie\Core\ValueObjects\IsStringValueObject;
use Apie\Fixtures\Identifiers\CollectionItemOwnedIdentifier;
use Apie\Fixtures\Identifiers\ImageFileIdentifier;
use Apie\Fixtures\Identifiers\OrderIdentifier;
use Apie\Fixtures\Identifiers\UserAutoincrementIdentifier;
use Apie\Fixtures\Identifiers\UserWithAddressIdentifier;
use Faker\Generator;
use ReflectionClass;
use ReflectionException;

/**
 * @template T of EntityInterface
 */
#[FakeMethod('createRandom')]
final class DecryptedAuthenticatedUser implements StringValueObjectInterface
{
    use IsStringValueObject;

    /** @var class-string<IdentifierInterface<T>> */
    private string $className;

    private BoundedContextId $boundedContextId;

    /** @var IdentifierInterface<T> */
    private IdentifierInterface $id;

    private int $expireTime;

    /**
     * @template U of EntityInterface
     * @param U $entity
     * @return DecryptedAuthenticatedUser<U>
     */
    public static function createFromEntity(
        EntityInterface  $entity,
        BoundedContextId $boundedContextId,
        int              $time
    ): self {
        return new self(
            get_class($entity->getId())
            . '/'
            . $boundedContextId
            . '/'
            . $entity->getId()
            . '/'
            . $time
        );
    }

    /**
     * @return class-string<IdentifierInterface<T>>
     */
    public function getIdentifierClassName(): string
    {
        return $this->className;
    }

    /**
     * @return class-string<T>
     */
    public function getEntityClassName(): string
    {
        return $this->className::getReferenceFor()->name;
    }

    public function isExpired(): bool
    {
        return $this->expireTime <= time();
    }

    public function getExpireTime(): int
    {
        return $this->expireTime;
    }

    public function getBoundedContextId(): BoundedContextId
    {
        return $this->boundedContextId;
    }

    /**
     * @return IdentifierInterface<T>
     */
    public function getId(): IdentifierInterface
    {
        return $this->id;
    }

    /**
     * @return DecryptedAuthenticatedUser<T>
     */
    public function refresh(int $expireTime): self
    {
        $res = clone $this;
        $res->expireTime = $expireTime;
        return $res;
    }

    protected function convert(string $input): string
    {
        list($className, $boundedContextId, $id, $expireTime) = array_pad(
            explode(
                '/',
                $input,
                4
            ),
            4,
            null
        );
        try {
            $refl = new ReflectionClass($className);
            if (!in_array(IdentifierInterface::class, $refl->getInterfaceNames(), true)) {
                throw new InvalidStringForValueObjectException($input, $this);
            }
        } catch (ReflectionException $previous) {
            throw new InvalidStringForValueObjectException($input, $this, $previous);
        }
        $this->className = $className;
        $this->boundedContextId = new BoundedContextId($boundedContextId);
        $this->id = new $className($id);
        $this->expireTime = (int)$expireTime;

        return $input;
    }

    public static function createRandom(Generator $factory): static
    {
        $classes = [
            CollectionItemOwnedIdentifier::class,
            ImageFileIdentifier::class,
            OrderIdentifier::class,
            UserAutoincrementIdentifier::class,
            UserWithAddressIdentifier::class,
        ];
        $class = $factory->randomElement($classes);
        if (!class_exists($class)) {
            throw new \LogicException('I need apie/fixtures to be able to make a random instance!');
        }
        $identifier = $class::createRandom($factory);
        return new static(
            get_class($identifier) . '/' . $factory->fakeClass(BoundedContextId::class) . '/' . $identifier->toNative() . '/' . (time() + 3600)
        );
    }
}
