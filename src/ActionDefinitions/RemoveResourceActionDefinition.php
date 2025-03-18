<?php
namespace Apie\Common\ActionDefinitions;

use Apie\Core\Attributes\RemovalCheck;
use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Core\Entities\EntityInterface;
use ReflectionClass;

/**
 * Action definition for removing a single resource by id in a specific bounded context.
 */
final class RemoveResourceActionDefinition implements ActionDefinitionInterface
{
    /**
     * @param ReflectionClass<EntityInterface> $resourceName
     */
    public function __construct(
        private readonly ReflectionClass $resourceName,
        private readonly BoundedContextId $boundedContextId
    ) {
    }

    /**
     * @return ReflectionClass<EntityInterface>
     */
    public function getResourceName(): ReflectionClass
    {
        return $this->resourceName;
    }

    public function getBoundedContextId(): BoundedContextId
    {
        return $this->boundedContextId;
    }

    /**
     * Business logic for removing single resources:
     * - Class should have a CanRemove attribute at top of the class.
     * - ContextConstants::REMOVE_OBJECT restrictions can be added if it is not wanted in some cases
     */
    public static function provideActionDefinitions(BoundedContext $boundedContext, ApieContext $apieContext, bool $runtimeChecks = false): array
    {
        $actionDefinitions = [];
        $getSingleContext = $apieContext->withContext(ContextConstants::REMOVE_OBJECT, true)
            ->registerInstance($boundedContext);
        foreach ($boundedContext->resources->filterOnApieContext($getSingleContext, $runtimeChecks) as $resource) {
            $foundAttribute = false;
            foreach ($resource->getAttributes(RemovalCheck::class) as $removalCheckAttribute) {
                $removalCheck = $removalCheckAttribute->newInstance();
                if ($removalCheck->applies($apieContext) && ($runtimeChecks || $removalCheck->isStaticCheck())) {
                    $foundAttribute = true;
                }
            }
            if ($foundAttribute) {
                $actionDefinitions[] = new RemoveResourceActionDefinition($resource, $boundedContext->getId());
            }
        }
        return $actionDefinitions;
    }
}
