<?php

namespace Apie\Common\Wrappers;

use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\Identifiers\IdentifierInterface;
use Apie\Core\ValueObjects\Exceptions\InvalidStringForValueObjectException;
use Apie\Core\ValueObjects\Interfaces\StringValueObjectInterface;
use Apie\Core\ValueObjects\IsStringValueObject;
use Exception;
use ReflectionClass;
use ReflectionNamedType;

/**
 * 'Id' of the ApieUserDecorator. Used to hydrate/rehydrate the currently used entity.
 *
 * @template T of EntityInterface
 */
final class ApieUserDecoratorIdentifier implements StringValueObjectInterface
{
    use IsStringValueObject;

    /**
     * @var ReflectionClass<T>
     */
    private ReflectionClass $class;

    private ?BoundedContextId $boundedContextId;

    /**
     * @var IdentifierInterface<T>
     */
    private IdentifierInterface $identifier;

    /**
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        return [
            'class' => $this->class->name,
            'boundedContextId' => $this->boundedContextId->toNative(),
            'identifier' => $this->identifier->toNative(),
            'identifierClass' => get_class($this->identifier),
            'internal' => $this->internal,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        $this->class = new ReflectionClass($data['class']);
        $this->boundedContextId = new BoundedContextId($data['boundedContextId']);
        $this->identifier = new ($data['identifierClass'])($data['identifier']);
        $this->internal = $data['internal'];
    }

    public static function validate(string $input): void
    {
        $split = explode('/', $input);
        if (count($split) !== 3) {
            throw new InvalidStringForValueObjectException($input, new ReflectionClass(__CLASS__));
        }
        try {
            $class = new ReflectionClass($split[0]);
            if (!$class->implementsInterface(EntityInterface::class)) {
                throw new InvalidStringForValueObjectException($input, new ReflectionClass(__CLASS__));
            }
            $getId = $class->getMethod('getId');
            $idType = $getId->getReturnType();
            if (!$idType instanceof ReflectionNamedType) {
                throw new InvalidTypeException($idType, 'ReflectionNamedType');
            }
            $className = $idType->getName();
            if ($split[1]) {
                new BoundedContextId($split[1]);
            }
            new $className($split[2]);
        } catch(Exception $exception) {
            throw new InvalidStringForValueObjectException($input, new ReflectionClass(__CLASS__), $exception);
        }
    }

    /**
     * @return ReflectionClass<T>
     */
    public function getClass(): ReflectionClass
    {
        return $this->class;
    }

    /**
     * @return BoundedContextId|null
     */
    public function getBoundedContextId(): ?BoundedContextId
    {
        return $this->boundedContextId;
    }

    /**
     * @return IdentifierInterface<T>
     */
    public function getIdentifier(): IdentifierInterface
    {
        return $this->identifier;
    }

    protected function convert(string $input): string
    {
        $split = explode('/', $input);
        $this->class = new ReflectionClass($split[0]);
        $this->boundedContextId = $split[1] ? new BoundedContextId($split[1]) : null;
        $getId = $this->class->getMethod('getId');
        $idType = $getId->getReturnType();
        if (!$idType instanceof ReflectionNamedType) {
            throw new InvalidTypeException($idType, 'ReflectionNamedType');
        }
        $className = $idType->getName();
        $this->identifier = new $className($split[2]);
        return $this->class->name . '/' . $this->boundedContextId . '/' . $this->identifier;
    }
}
