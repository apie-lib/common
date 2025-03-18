<?php
namespace Apie\Common\ActionDefinitions;

use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\PropertyToFieldMetadataUtil;
use Apie\Core\Utils\ConverterUtils;
use Apie\TypeConverter\ReflectionTypeFactory;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionClass;
use ReflectionNamedType;

class DownloadFilesActionDefinition implements ActionDefinitionInterface
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
     * - Add route definition if there's a getter with a UploadedFileInterface reference or no typehint (resource).
     */
    public static function provideActionDefinitions(
        BoundedContext $boundedContext,
        ApieContext $apieContext,
        bool $runtimeChecks = false
    ): array {
        $actionDefinitions = [];
        $resourceActionContext = $apieContext;
        $uploadedFileType = ReflectionTypeFactory::createReflectionType(UploadedFileInterface::class);
        assert($uploadedFileType instanceof ReflectionNamedType);
        foreach ($boundedContext->resources->filterOnApieContext($resourceActionContext, $runtimeChecks) as $resource) {
            if (PropertyToFieldMetadataUtil::hasPropertyWithType(
                ConverterUtils::toReflectionType($resource),
                $uploadedFileType,
                $resourceActionContext
            )) {
                $actionDefinitions[] = new self($resource, $boundedContext->getId());
            }
        }
        // @phpstan-ignore return.type
        return $actionDefinitions;
    }
}
