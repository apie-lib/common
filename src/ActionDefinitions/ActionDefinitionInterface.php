<?php
namespace Apie\Common\ActionDefinitions;

use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\Context\ApieContext;

interface ActionDefinitionInterface
{
    /**
     * @return array<int, static>
     */
    public static function provideActionDefinitions(BoundedContext $boundedContext, ApieContext $apieContext, bool $runtimeChecks = false): array;
}
