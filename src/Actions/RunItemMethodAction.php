<?php
namespace Apie\Common\Actions;

use Apie\Common\ApieFacade;
use Apie\Common\ApieFacadeAction;
use Apie\Common\ContextConstants;
use Apie\Common\Utils;
use Apie\Core\Context\ApieContext;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\Lists\ItemHashmap;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * Runs a method from  a resource (and persist resource afterwards).
 */
final class RunItemMethodAction implements ApieFacadeAction
{
    public function __construct(private readonly ApieFacade $apieFacade)
    {
    }
    /**
     * @param array<string|int, mixed> $rawContents
     */
    public function __invoke(ApieContext $context, array $rawContents): ItemHashmap
    {
        $resourceClass = new ReflectionClass($context->getContext(ContextConstants::RESOURCE_NAME));
        $id = $context->getContext(ContextConstants::RESOURCE_ID);
        if (!$resourceClass->implementsInterface(EntityInterface::class)) {
            throw new InvalidTypeException($resourceClass->name, 'EntityInterface');
        }
        $resource = $this->apieFacade->find(Utils::entityClassToIdentifier($resourceClass)->newInstance($id));
        $method = new ReflectionMethod(
            $context->getContext(ContextConstants::METHOD_CLASS),
            $context->getContext(ContextConstants::METHOD_NAME)
        );
        $result = $this->apieFacade->denormalizeOnMethodCall(
            $context->getContext(ContextConstants::RAW_CONTENTS),
            $resource,
            $method,
            $context
        );
        // TODO: persist $resource
        if (self::shouldReturnResource($method)) {
            $result = $resource;
        }

        return $this->apieFacade->normalize($result, $context);
    }

    public static function shouldReturnResource(ReflectionMethod $method): bool
    {
        $returnType = $method->getReturnType();
        if ($returnType instanceof ReflectionNamedType && 'void' === $returnType->getName()) {
            return true;
        }
        if ($method->getNumberOfParameters() === 0) {
            return false;
        }
                
        return str_starts_with($method->name, 'add') || str_starts_with($method->name, 'remove');
    }

    public static function getDisplayNameForMethod(ReflectionMethod $method): string
    {
        if ($method->getNumberOfParameters() > 0) {
            if (str_starts_with($method->name, 'remove')) {
                return lcfirst(substr($method->name, strlen('remove')));
            }
            if (str_starts_with($method->name, 'add')) {
                return lcfirst(substr($method->name, strlen('add')));
            }
        }
        return $method->name;
    }
}
