<?php
namespace Apie\Common\Actions;

use Apie\Common\Events\ApieResourceCreated;
use Apie\Common\IntegrationTestLogger;
use Apie\Core\Actions\ActionInterface;
use Apie\Core\Actions\ActionResponse;
use Apie\Core\Actions\ActionResponseStatus;
use Apie\Core\Actions\ActionResponseStatusList;
use Apie\Core\Actions\ApieFacadeInterface;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Exceptions\IndexNotFoundException;
use Apie\Core\Lists\StringList;
use Apie\Core\Utils\EntityUtils;
use Apie\Core\ValueObjects\Utils;
use Exception;
use LogicException;
use ReflectionClass;

/**
 * Action to create a new object.
 */
final class CreateObjectAction implements ActionInterface
{
    public function __construct(private readonly ApieFacadeInterface $apieFacade)
    {
    }

    public static function isAuthorized(ApieContext $context, bool $runtimeChecks, bool $throwError = false): bool
    {
        $refl = new ReflectionClass($context->getContext(ContextConstants::RESOURCE_NAME, $throwError));
        if (!$context->appliesToContext($refl, $runtimeChecks, $throwError ? new LogicException('Class access is not allowed') : null)) {
            return false;
        }
        if (EntityUtils::isPolymorphicEntity($refl) && $runtimeChecks && $context->hasContext(ContextConstants::RAW_CONTENTS)) {
            $rawContents = Utils::toArray($context->getContext(ContextConstants::RAW_CONTENTS, $throwError));
            try {
                $refl = EntityUtils::findClass($rawContents, $refl);
            } catch (IndexNotFoundException) {
            }
        }
        $constructor = $refl->getConstructor();
        if ($constructor && !$context->appliesToContext($constructor, $runtimeChecks, $throwError ? new LogicException('Class instantiation is not allowed') : null)) {
            return false;
        }
        return true;
    }
    
    /**
     * @param array<string|int, mixed> $rawContents
     */
    public function __invoke(ApieContext $context, array $rawContents): ActionResponse
    {
        $context->withContext(ContextConstants::RAW_CONTENTS, $rawContents)
            ->withContext(ContextConstants::APIE_ACTION, __CLASS__)
            ->checkAuthorization();
        try {
            $resource = $this->apieFacade->denormalizeNewObject(
                $rawContents,
                $context->getContext(ContextConstants::RESOURCE_NAME),
                $context
            );
        } catch (Exception $error) {
            IntegrationTestLogger::logException($error);
            return ActionResponse::createClientError($this->apieFacade, $context, $error);
        }
        $context = $context->withContext(ContextConstants::RESOURCE, $resource);
        $resource = $this->apieFacade->persistNew($resource, new BoundedContextId($context->getContext(ContextConstants::BOUNDED_CONTEXT_ID)));
        $context = $context->withContext(ContextConstants::RESOURCE, $resource);
        $context->dispatchEvent(new ApieResourceCreated($resource));
        return ActionResponse::createCreationSuccess($this->apieFacade, $context, $resource, $resource);
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
            ActionResponseStatus::CREATED,
            ActionResponseStatus::CLIENT_ERROR,
            ActionResponseStatus::PERISTENCE_ERROR
        ]);
    }

    /**
     * @param ReflectionClass<object> $class
     */
    public static function getDescription(ReflectionClass $class): string
    {
        return 'Creates an instance of ' . $class->getShortName();
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
            ContextConstants::CREATE_OBJECT => true,
            ContextConstants::RESOURCE_NAME => $class->name,
        ];
    }
}
