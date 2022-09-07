<?php
namespace Apie\Common\Interfaces;

use Apie\Common\RouteDefinitions\ActionHashmap;
use Apie\Core\Actions\ApieFacadeInterface as ActionsApieFacadeInterface;
use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;

interface ApieFacadeInterface extends ActionsApieFacadeInterface
{
    public function getActionsForBoundedContext(
        BoundedContextId|BoundedContext $boundedContext,
        ApieContext $apieContext
    ): ActionHashmap;
}
