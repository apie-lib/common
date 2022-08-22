<?php
namespace Apie\Common\Actions;

use Apie\Common\ApieFacade;
use Apie\Common\ApieFacadeAction;
use Apie\Common\ContextConstants;
use Apie\Core\Actions\ActionResponse;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\IdentifierUtils;
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
    public function __invoke(ApieContext $context, array $rawContents): ActionResponse
    {
        $resourceClass = new ReflectionClass($context->getContext(ContextConstants::RESOURCE_NAME));
        if (!$resourceClass->implementsInterface(EntityInterface::class)) {
            throw new InvalidTypeException($resourceClass->name, 'EntityInterface');
        }
        $method = new ReflectionMethod(
            $context->getContext(ContextConstants::METHOD_CLASS),
            $context->getContext(ContextConstants::METHOD_NAME)
        );
        if ($method->isStatic()) {
            $resource = null;
        } else {
            $id = $context->getContext(ContextConstants::RESOURCE_ID);
            $resource = $this->apieFacade->find(
                IdentifierUtils::entityClassToIdentifier($resourceClass)->newInstance($id),
                new BoundedContextId($context->getContext(ContextConstants::BOUNDED_CONTEXT_ID))
            );
        }

        $result = $this->apieFacade->denormalizeOnMethodCall(
            $rawContents,
            $resource,
            $method,
            $context
        );
        if ($resource !== null) {
            $resource = $this->apieFacade->persistExisting(
                $resource,
                new BoundedContextId($context->getContext(ContextConstants::BOUNDED_CONTEXT_ID))
            );
        }
        if (self::shouldReturnResource($method)) {
            $result = $resource;
        }
        return ActionResponse::createRunSuccess($this->apieFacade, $context, $result, $resource);
    }

    /**
     * Returns true if we should not return the return value of the method, but should return the return value of the resource.
     * This is the case if:
     * - The method returns void
     * - The method call starts with 'add' or 'remove' and has arguments.
     */
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

    /**
     * Returns a string how we should display the method. For example we remove 'add' or 'remove' from the string.
     */
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
