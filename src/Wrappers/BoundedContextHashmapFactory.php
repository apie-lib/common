<?php
namespace Apie\Common\Wrappers;

use Apie\ApieBundle\DependencyInjection\Configuration;
use Apie\Common\ValueObjects\EntityNamespace;
use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\BoundedContext\BoundedContextId;

/**
 * Creates a BoundedContextHashmap instance from the ApieBundle configuration.
 *
 * @see Configuration in apie/apie-bundle for Symfony
 * @see config.php in apie/laravel-apie for Laravel
 */
final class BoundedContextHashmapFactory
{
    /**
     * @param array<string, mixed> $boundedContexts
     */
    public function __construct(private readonly array $boundedContexts)
    {
    }

    public function create(): BoundedContextHashmap
    {
        $result = [];
        foreach ($this->boundedContexts as $boundedContextId => $boundedContextConfig) {
            $contextId = new BoundedContextId($boundedContextId);
            $namespace = new EntityNamespace($boundedContextConfig['entities_namespace']);
            $classes = $namespace->getClasses($boundedContextConfig['entities_folder']);
            $namespace = new EntityNamespace($boundedContextConfig['actions_namespace']);
            $methods = $namespace->getMethods($boundedContextConfig['actions_folder']);
            $result[$boundedContextId] = new BoundedContext(
                $contextId,
                $classes,
                $methods
            );
        }
        return new BoundedContextHashmap($result);
    }
}
