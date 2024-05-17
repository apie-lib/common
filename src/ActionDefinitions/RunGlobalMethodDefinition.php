<?php
namespace Apie\Common\ActionDefinitions;

use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use ReflectionMethod;

/**
 * Action definition for modifying a single resource by id in a specific bounded context.
 */
final class RunGlobalMethodDefinition implements ActionDefinitionInterface
{
    public function __construct(
        private readonly ReflectionMethod $method,
        private readonly BoundedContextId $boundedContextId
    ) {
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
     * - Check apie context with ContextConstants::GLOBAL_METHOD
     */
    public static function provideActionDefinitions(BoundedContext $boundedContext, ApieContext $apieContext, bool $runtimeChecks = false): array
    {
        $actionDefinitions = [];
        $globalActionContext = $apieContext->withContext(ContextConstants::GLOBAL_METHOD, true);
        foreach ($boundedContext->actions->filterOnApieContext($globalActionContext, $runtimeChecks) as $action) {
            $actionDefinitions[] = new RunGlobalMethodDefinition($action, $boundedContext->getId());
        }

        return $actionDefinitions;
    }
}
