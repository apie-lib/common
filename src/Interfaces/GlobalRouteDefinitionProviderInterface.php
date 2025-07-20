<?php
namespace Apie\Common\Interfaces;

use Apie\Common\RouteDefinitions\ActionHashmap;

interface GlobalRouteDefinitionProviderInterface extends RouteDefinitionProviderInterface
{
   public function getGlobalRoutes(): ActionHashmap;
}