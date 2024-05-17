<?php
namespace Apie\Common\ActionDefinitions;

use Apie\Common\Actions\CreateObjectAction;
use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Entities\PolymorphicEntityInterface;
use Apie\Core\Metadata\MetadataFactory;
use ReflectionClass;

/**
 * Definition for creating a single resource class in a specific bounded context.
 */
final class CreateResourceActionDefinition implements ActionDefinitionInterface
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
     * Business logic for creating resources:
     * - constructor should not have a required id argument
     * - ContextConstants::CREATE_OBJECT restrictions can be added if it is not wanted
     * - constructor is private or protected, but resource is not implementing PolymorphicEntityInterface
     */
    public static function provideActionDefinitions(BoundedContext $boundedContext, ApieContext $apieContext, bool $runtimeChecks = false): array
    {
        $actionDefinitions = [];
        $postContext = $apieContext->withContext(ContextConstants::CREATE_OBJECT, true)
            ->registerInstance($boundedContext);
        foreach ($boundedContext->resources->filterOnApieContext($postContext, $runtimeChecks) as $resource) {
            if ($runtimeChecks && !CreateObjectAction::isAuthorized($postContext->withContext(ContextConstants::RESOURCE_NAME, $resource->name), true)) {
                continue;
            }
            $constructor = $resource->getConstructor();
            if ($constructor && !$constructor->isPublic() && !$resource->implementsInterface(PolymorphicEntityInterface::class)) {
                continue;
            }
            $metadata = MetadataFactory::getCreationMetadata($resource, $postContext);
            $hashmap = $metadata->getHashmap();
            if (!isset($hashmap['id']) || !$hashmap['id']->isRequired()) {
                $actionDefinitions[] = new CreateResourceActionDefinition($resource, $boundedContext->getId());
            }
        }

        return $actionDefinitions;
    }
}
