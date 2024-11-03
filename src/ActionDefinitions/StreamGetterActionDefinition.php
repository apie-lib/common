<?php
namespace Apie\Common\ActionDefinitions;

use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Entities\PolymorphicEntityInterface;
use Apie\Core\TypeUtils;
use Apie\Core\Utils\EntityUtils;
use ReflectionClass;
use ReflectionMethod;

/**
 * Action definition for getters that could return an UploadedFile or resource type.
 */
class StreamGetterActionDefinition implements ActionDefinitionInterface
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
     * - Return value should be resource or UploadedFile
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
                foreach ($resourceActionContext->getApplicableGetters($actualClass, $runtimeChecks) as $method) {
                    $returnType = $method instanceof ReflectionMethod ? $method->getReturnType() : $method->getType();
                    if (TypeUtils::couldBeAStream($returnType)) {
                        if ($method instanceof ReflectionMethod) {
                            $definition = new StreamGetterActionDefinition(
                                $resource,
                                $method,
                                $boundedContext->getId()
                            );
                            $actionDefinitions[] = $definition;
                        }// TODO $method instanceof ReflectionProperty
                    }
                }
            }
        }
        return array_values($actionDefinitions);
    }
}
