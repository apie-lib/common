<?php
namespace Apie\Common;

use Apie\Core\Entities\EntityInterface;
use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\Identifiers\IdentifierInterface;
use ReflectionClass;
use ReflectionNamedType;

final class Utils
{
    private function __construct()
    {
    }

    /**
     * @template T of EntityInterface
     * @param ReflectionClass<IdentifierInterface<T>>|IdentifierInterface<T>
     * @return ReflectionClass<T>
     */
    public static function identifierToEntityClass(ReflectionClass|IdentifierInterface $identifier): ReflectionClass
    {
        if ($identifier instanceof IdentifierInterface) {
            $identifier = new ReflectionClass($identifier);
        }
        return $identifier->getMethod('getReferenceFor')->invoke(null);
    }

    /**
     * @template T of EntityInterface
     * @param ReflectionClass<T>|T
     * @return ReflectionClass<IdentifierInterface<T>>
     */
    public static function entityClassToIdentifier(ReflectionClass|EntityInterface $identifier): ReflectionClass
    {
        if ($identifier instanceof EntityInterface) {
            $identifier = new ReflectionClass($identifier);
        }
        $returnType = $identifier->getMethod('getId')->getReturnType();
        if (!($returnType instanceof ReflectionNamedType)) {
            throw new InvalidTypeException($returnType, 'ReflectionNamedType');
        }
        return new ReflectionClass($returnType->getName());
    }
}
