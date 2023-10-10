<?php
namespace Apie\Common\ActionDefinitions;

use Apie\Common\ContextConstants;
use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\Entities\EntityInterface;
use ReflectionClass;

/**
 * Action definition for getting a single resource by id in a specific bounded context.
 */
final class GetResourceActionDefinition implements ActionDefinitionInterface
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
     * Business logic for getting single resources:
     * - ContextConstants::GET_OBJECT restrictions can be added if it is not wanted
     */
    public static function provideActionDefinitions(BoundedContext $boundedContext, ApieContext $apieContext, bool $runtimeChecks = false): array
    {
        $actionDefinitions = [];
        $getSingleContext = $apieContext->withContext(ContextConstants::GET_OBJECT, true)
            ->registerInstance($boundedContext);
        foreach ($boundedContext->resources->filterOnApieContext($getSingleContext, $runtimeChecks) as $resource) {
            $actionDefinitions[] = new GetResourceActionDefinition($resource, $boundedContext->getId());
        }
        return $actionDefinitions;
    }
}
