<?php
namespace Apie\Common\RouteDefinitions;

use Apie\Common\Interfaces\GlobalRouteDefinitionProviderInterface;
use Apie\Common\Interfaces\RouteDefinitionProviderInterface;
use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\Context\ApieContext;

class ChainedRouteDefinitionsProvider implements GlobalRouteDefinitionProviderInterface
{
    /**
     * @var RouteDefinitionProviderInterface[]
     */
    private array $routeDefinitions;

    public function __construct(RouteDefinitionProviderInterface... $routeDefinitions)
    {
        $this->routeDefinitions = $routeDefinitions;
    }

    public function getGlobalRoutes(): ActionHashmap
    {
        $actionHashmap = new ActionHashmap();
        foreach ($this->routeDefinitions as $routeDefinition) {
            if ($routeDefinition instanceof GlobalRouteDefinitionProviderInterface) {    
                $actionHashmap = $actionHashmap->merge($routeDefinition->getGlobalRoutes());
            }
        }
        return $actionHashmap;
    }

    public function getActionsForBoundedContext(BoundedContext $boundedContext, ApieContext $apieContext): ActionHashmap
    {
        $actionHashmap = new ActionHashmap();
        foreach ($this->routeDefinitions as $routeDefinition) {
            $actionHashmap = $actionHashmap->merge($routeDefinition->getActionsForBoundedContext($boundedContext, $apieContext));
        }
        return $actionHashmap;
    }
}
