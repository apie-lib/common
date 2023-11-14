<?php
namespace Apie\Common\Actions;

use Apie\Common\ContextConstants;
use Apie\Common\IntegrationTestLogger;
use Apie\Core\Actions\ActionInterface;
use Apie\Core\Actions\ActionResponse;
use Apie\Core\Actions\ActionResponseStatus;
use Apie\Core\Actions\ActionResponseStatusList;
use Apie\Core\Actions\ApieFacadeInterface;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Exceptions\EntityNotFoundException;
use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\IdentifierUtils;
use Apie\Core\Lists\StringList;
use Apie\Core\ValueObjects\Exceptions\InvalidStringForValueObjectException;
use ReflectionClass;

/**
 * Action to remove an existing object.
 */
final class RemoveObjectAction implements ActionInterface
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
        $id = $context->getContext(ContextConstants::RESOURCE_ID);
        if (!$resourceClass->implementsInterface(EntityInterface::class)) {
            throw new InvalidTypeException($resourceClass->name, 'EntityInterface');
        }
        $boundedContextId = new BoundedContextId($context->getContext(ContextConstants::BOUNDED_CONTEXT_ID));
        try {
            $resource = $this->apieFacade->find(
                IdentifierUtils::entityClassToIdentifier($resourceClass)->newInstance($id),
                $boundedContextId
            );
        } catch (InvalidStringForValueObjectException|EntityNotFoundException $error) {
            IntegrationTestLogger::logException($error);
            return ActionResponse::createClientError($this->apieFacade, $context, $error);
        }
        $context = $context->withContext(ContextConstants::RESOURCE, $resource);
        $this->apieFacade->removeExisting($resource, $boundedContextId);

        return ActionResponse::createRemovedSuccess($this->apieFacade, $context);
    }

    /**
     * @return ReflectionClass<EntityInterface>
     */
    public static function getInputType(ReflectionClass $class): ReflectionClass
    {
        return $class;
    }

    /**
     * @return ReflectionClass<EntityInterface>
     */
    public static function getOutputType(ReflectionClass $class): ReflectionClass
    {
        return $class;
    }

    public static function getPossibleActionResponseStatuses(): ActionResponseStatusList
    {
        return new ActionResponseStatusList([
            ActionResponseStatus::DELETED,
            ActionResponseStatus::CLIENT_ERROR,
            ActionResponseStatus::PERISTENCE_ERROR
        ]);
    }

    /**
     * @param ReflectionClass<object> $class
     */
    public static function getDescription(ReflectionClass $class): string
    {
        return 'Removes an instance of ' . $class->getShortName();
    }
    
    /**
     * @param ReflectionClass<object> $class
     */
    public static function getTags(ReflectionClass $class): StringList
    {
        return new StringList([$class->getShortName(), 'resource']);
    }

    /**
     * @param ReflectionClass<object> $class
     */
    public static function getRouteAttributes(ReflectionClass $class): array
    {
        return [
            ContextConstants::EDIT_OBJECT => true,
            ContextConstants::RESOURCE_NAME => $class->name,
            ContextConstants::DISPLAY_FORM => true,
        ];
    }
}
