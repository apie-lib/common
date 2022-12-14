<?php
namespace Apie\Common\Actions;

use Apie\Common\ContextConstants;
use Apie\Core\Actions\ActionResponse;
use Apie\Core\Actions\ActionResponseStatus;
use Apie\Core\Actions\ActionResponseStatusList;
use Apie\Core\Actions\ApieFacadeInterface;
use Apie\Core\Actions\MethodActionInterface;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\IdentifierUtils;
use Apie\Core\Lists\StringList;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * Runs a method from  a resource (and persist resource afterwards).
 */
final class RunItemMethodAction implements MethodActionInterface
{
    public function __construct(private readonly ApieFacadeInterface $apieFacade)
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

    public static function getInputType(ReflectionClass $class, ?ReflectionMethod $method = null): ReflectionMethod
    {
        assert($method instanceof ReflectionMethod);
        return $method;
    }

    public static function getOutputType(ReflectionClass $class, ?ReflectionMethod $method = null): ReflectionMethod|ReflectionClass
    {
        assert($method instanceof ReflectionMethod);
        if (RunItemMethodAction::shouldReturnResource($method)) {
            return $class;
        }
        return $method;
    }

    public static function getPossibleActionResponseStatuses(?ReflectionMethod $method = null): ActionResponseStatusList
    {
        assert($method instanceof ReflectionMethod);
        $list = [ActionResponseStatus::SUCCESS];

        if (!empty($method->getParameters())) {
            $list[] = ActionResponseStatus::CLIENT_ERROR;
        }
        if (!$method->isStatic()) {
            $list[] = ActionResponseStatus::NOT_FOUND;
        }
        return new ActionResponseStatusList($list);
    }

    public static function getDescription(ReflectionClass $class, ?ReflectionMethod $method = null): string
    {
        assert($method instanceof ReflectionMethod);
        $name = self::getDisplayNameForMethod($method);
        if (str_starts_with($method->name, 'add')) {
            return 'Adds ' . $name . ' to ' . $class->getShortName();
        }
        if (str_starts_with($method->name, 'remove')) {
            return 'Removes ' . $name . ' from ' . $class->getShortName();
        }
        return 'Runs method ' . $name . ' on a ' . $class->getShortName() . ' with a specific id';
    }

    public function getOperationId(): string
    {
        return 'get-single-' . $this->className->getShortName() . '-run-' . $this->method->name;
    }
    
    public static function getTags(ReflectionClass $class, ?ReflectionMethod $method = null): StringList
    {
        $className = $class->getShortName();
        $declared = $method ? $method->getDeclaringClass()->getShortName() : $className;
        if ($className !== $declared) {
            return new StringList([$className, $declared, 'action']);
        }
        return new StringList([$className, 'action']);
    }

    public static function getRouteAttributes(ReflectionClass $class, ?ReflectionMethod $method = null): array
    {
        return
        [
            ContextConstants::GET_OBJECT => true,
            ContextConstants::RESOURCE_METHOD => true,
            ContextConstants::RESOURCE_NAME => $class->name,
            ContextConstants::METHOD_CLASS => $method->getDeclaringClass()->name,
            ContextConstants::METHOD_NAME => $method->name,
        ];
    }
}
