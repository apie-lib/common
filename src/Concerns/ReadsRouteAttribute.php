<?php
namespace Apie\Common\Concerns;

use Apie\Core\Attributes\Route;

trait ReadsRouteAttribute
{
    private ?Route $routeAttribute;

    private bool $routeCalculated = false;

    private function getRouteAttribute(): ?Route
    {
        if (!$this->routeCalculated) {
            $this->routeAttribute = null;
            $this->routeCalculated = true;
            foreach ($this->method->getAttributes(Route::class) as $routeAttribute) {
                $route = $routeAttribute->newInstance();
                if (in_array($route->target, [Route::ALL, self::CURRENT_TARGET])) {
                    if (!$this->routeAttribute || $route->target === self::CURRENT_TARGET) {
                        $this->routeAttribute = $route;
                    }
                }
            }
        }
        return $this->routeAttribute;
    }
}
