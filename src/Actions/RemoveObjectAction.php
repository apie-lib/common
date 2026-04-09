<?php
namespace Apie\Common\Actions;

use Apie\Common\Events\ApieResourceRemoved;
use Apie\Common\IntegrationTestLogger;
use Apie\Common\Other\LockUtil;
use Apie\Core\Actions\ActionInterface;
use Apie\Core\Actions\ActionResponse;
use Apie\Core\Actions\ActionResponseStatus;
use Apie\Core\Actions\ActionResponseStatusList;
use Apie\Core\Actions\ApieFacadeInterface;
use Apie\Core\Attributes\Description;
use Apie\Core\Attributes\RemovalCheck;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Exceptions\EntityNotFoundException;
use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\IdentifierUtils;
use Apie\Core\Lists\StringList;
use Apie\Core\Utils\EntityUtils;
use Apie\Core\ValueObjects\Exceptions\InvalidStringForValueObjectException;
use LogicException;
use ReflectionClass;

/**
 * Action to remove an existing object.
 */
final class RemoveObjectAction implements ActionInterface
{
    public function __construct(private readonly ApieFacadeInterface $apieFacade)
    {
    }

    public static function isAuthorized(ApieContext $context, bool $runtimeChecks, bool $throwError = false): bool
    {
        $refl = new ReflectionClass($context->getContext(ContextConstants::RESOURCE_NAME, $throwError));
        if (EntityUtils::isPolymorphicEntity($refl) && $runtimeChecks && $context->hasContext(ContextConstants::RESOURCE)) {
            $refl = new ReflectionClass($context->getContext(ContextConstants::RESOURCE, $throwError));
        }
        if (!$context->appliesToContext($refl, $runtimeChecks, $throwError ? new LogicException('Class does not allow it') : null)) {
            return false;
        }
        $returnValue = false;
        foreach ($refl->getAttributes(RemovalCheck::class) as $removeAttribute) {
            $returnValue = true;
            $removeCheck = $removeAttribute->newInstance();
            if ($removeCheck->isStaticCheck() && !$removeCheck->applies($context)) {
                return false;
            }
            if ($runtimeChecks && $removeCheck->isRuntimeCheck() && !$removeCheck->applies($context)) {
                return false;
            }
        }
        return $returnValue;
    }
    
    /**
     * @param array<string|int, mixed> $rawContents
     */
    public function __invoke(ApieContext $context, array $rawContents): ActionResponse
    {
        $context->withContext(ContextConstants::APIE_ACTION, __CLASS__)->checkAuthorization();
        $resourceClass = new ReflectionClass($context->getContext(ContextConstants::RESOURCE_NAME));
        $id = $context->getContext(ContextConstants::RESOURCE_ID);
        if (!$resourceClass->implementsInterface(EntityInterface::class)) {
            throw new InvalidTypeException($resourceClass->name, 'EntityInterface');
        }
        $lock = LockUtil::createLock(
            $context,
            [ContextConstants::BOUNDED_CONTEXT_ID, ContextConstants::RESOURCE_NAME, ContextConstants::RESOURCE_ID],
            write: true
        );
        try {
            $boundedContextId = new BoundedContextId($context->getContext(ContextConstants::BOUNDED_CONTEXT_ID));
            try {
                $resource = $this->apieFacade->find(
                    IdentifierUtils::idStringToIdentifier($id, $context),
                    $boundedContextId
                );
            } catch (InvalidStringForValueObjectException|EntityNotFoundException $error) {
                IntegrationTestLogger::logException($error);
                return ActionResponse::createClientError($this->apieFacade, $context, $error);
            }
            $context = $context->withContext(ContextConstants::RESOURCE, $resource);

            if (!$lock->isAcquired()) {
                throw new \LogicException('Lock was released before modification was finished!');
            }
            $this->apieFacade->removeExisting($resource, $boundedContextId);
        } finally {
            $lock->release();
        }
        
        $context->dispatchEvent(new ApieResourceRemoved($resource, $context));

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
        $description = 'Removes an instance of ' . $class->getShortName();
        foreach ($class->getAttributes(Description::class) as $attribute) {
            $description .= '. ' . $attribute->newInstance()->description;
        }

        return $description;
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
