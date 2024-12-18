<?php
namespace Apie\Common\Actions;

use Apie\Core\Actions\ActionInterface;
use Apie\Core\Actions\ActionResponse;
use Apie\Core\Actions\ActionResponseStatus;
use Apie\Core\Actions\ActionResponseStatusList;
use Apie\Core\Actions\ApieFacadeInterface;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\IdentifierUtils;
use Apie\Core\Lists\PermissionList;
use Apie\Core\Lists\StringList;
use Apie\Core\Permissions\PermissionInterface;
use Apie\Core\Permissions\RequiresPermissionsInterface;
use Apie\Core\Utils\EntityUtils;
use LogicException;
use ReflectionClass;

/**
 * Action to get a single item resource.
 */
final class GetItemAction implements ActionInterface
{
    public function __construct(private readonly ApieFacadeInterface $apieFacade)
    {
    }

    public static function isAuthorized(ApieContext $context, bool $runtimeChecks, bool $throwError = false): bool
    {
        $refl = new ReflectionClass($context->getContext(ContextConstants::RESOURCE_NAME, $throwError));
        $resource = $context->getContext(ContextConstants::RESOURCE, false);
        if ($resource instanceof RequiresPermissionsInterface) {
            $requiredPermissions = $resource->getRequiredPermissions();
            $user = $context->getContext(ContextConstants::AUTHENTICATED_USER, false);
            if ($user instanceof PermissionInterface) {
                $hasPermisions = $user->getPermissionIdentifiers();
                return $hasPermisions->hasOverlap($requiredPermissions);
            }
            return $requiredPermissions->hasOverlap(new PermissionList(['']));
        }
        if (EntityUtils::isPolymorphicEntity($refl) && $runtimeChecks && $resource) {
            $refl = new ReflectionClass($resource);
        }
        return $context->appliesToContext($refl, $runtimeChecks, $throwError ? new LogicException('Operation is not allowed') : null);
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
        $result = $this->apieFacade->find(
            IdentifierUtils::entityClassToIdentifier($resourceClass)->newInstance($id),
            new BoundedContextId($context->getContext(ContextConstants::BOUNDED_CONTEXT_ID))
        );
        $context = $context->withContext(ContextConstants::RESOURCE, $result);
        $context->withContext(ContextConstants::APIE_ACTION, __CLASS__)->checkAuthorization();
        return ActionResponse::createRunSuccess($this->apieFacade, $context, $result, $result);
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
            ActionResponseStatus::SUCCESS,
            ActionResponseStatus::AUTHORIZATION_ERROR,
            ActionResponseStatus::NOT_FOUND
        ]);
    }

    public static function getDescription(ReflectionClass $class): string
    {
        return 'Gets a resource of ' . $class->getShortName() . ' with a specific id';
    }
    
    public static function getTags(ReflectionClass $class): StringList
    {
        return new StringList([$class->getShortName(), 'resource']);
    }

    public static function getRouteAttributes(ReflectionClass $class): array
    {
        return [
            ContextConstants::GET_OBJECT => true,
            ContextConstants::RESOURCE_NAME => $class->name,
        ];
    }
}
