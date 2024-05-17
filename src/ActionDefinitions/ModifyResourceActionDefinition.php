<?php
namespace Apie\Common\ActionDefinitions;

use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Metadata\MetadataFactory;
use ReflectionClass;

/**
 * Action definition for modifying a single resource by id in a specific bounded context.
 */
final class ModifyResourceActionDefinition implements ActionDefinitionInterface
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
     * Business logic:
     * - Check apie context with ContextConstants::EDIT_OBJECT
     * - A writable public property or setter should be defined in the class.
     */
    public static function provideActionDefinitions(BoundedContext $boundedContext, ApieContext $apieContext, bool $runtimeChecks = false): array
    {
        $actionDefinitions = [];
        $patchSingleContext = $apieContext->withContext(ContextConstants::EDIT_OBJECT, true)
            ->registerInstance($boundedContext);
        foreach ($boundedContext->resources->filterOnApieContext($patchSingleContext, $runtimeChecks) as $resource) {
            $metadata = MetadataFactory::getModificationMetadata($resource, $patchSingleContext);
            if ($metadata->getHashmap()->count()) {
                $actionDefinitions[] = new ModifyResourceActionDefinition($resource, $boundedContext->getId());
            }
        }

        return $actionDefinitions;
    }
}
