<?php
namespace Apie\Common\ActionDefinitions;

use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Entities\PolymorphicEntityInterface;
use Apie\Core\Utils\EntityUtils;
use ReflectionClass;
use ReflectionMethod;

/**
 * Action definition for modifying a single resource by id in a specific bounded context.
 */
final class RunResourceMethodDefinition implements ActionDefinitionInterface
{
    /**
     * @param ReflectionClass<EntityInterface> $resourceName
     */
    public function __construct(
        private readonly ReflectionClass $resourceName,
        private readonly ReflectionMethod $method,
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

    public function getMethod(): ReflectionMethod
    {
        return $this->method;
    }

    /**
     * Business logic:
     * - Check apie context with ContextConstants::RESOURCE_METHOD
     */
    public static function provideActionDefinitions(BoundedContext $boundedContext, ApieContext $apieContext, bool $runtimeChecks = false): array
    {
        $actionDefinitions = [];
        $resourceActionContext = $apieContext->withContext(ContextConstants::RESOURCE_METHOD, true);
        foreach ($boundedContext->resources->filterOnApieContext($resourceActionContext, $runtimeChecks) as $resource) {
            $resourceList = [$resource];
            if (in_array(PolymorphicEntityInterface::class, $resource->getInterfaceNames())) {
                if ($runtimeChecks) {
                    if ($apieContext->hasContext(ContextConstants::RESOURCE)) {
                        $resourceList = [new ReflectionClass($apieContext->getContext(ContextConstants::RESOURCE))];
                    }
                } else {
                    $resourceList = EntityUtils::getDiscriminatorClasses($resource);
                }
            }
            foreach ($resourceList as $actualClass) {
                foreach ($resourceActionContext->getApplicableMethods($actualClass, $runtimeChecks) as $method) {
                    $definition = new RunResourceMethodDefinition(
                        $resource,
                        $method,
                        $boundedContext->getId()
                    );
                    $actionDefinitions[] = $definition;
                }
            }
        }
        return $actionDefinitions;
    }
}
