<?php
namespace Apie\Common\Wrappers;

use Apie\ApieBundle\DependencyInjection\Configuration;
use Apie\Common\ValueObjects\EntityNamespace;
use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\BoundedContext\BoundedContextId;
use Symfony\Component\Finder\Finder;

/**
 * Creates a BoundedContextHashmap instance from the ApieBundle configuration.
 *
 * @see Configuration in apie/apie-bundle for Symfony
 * @see resources/apie.php in apie/laravel-apie for Laravel
 */
final class BoundedContextHashmapFactory
{
    /**
     * @param array<string, mixed> $boundedContexts
     * @param array<string, string> $scanBoundedContexts
     */
    public function __construct(
        private readonly array $boundedContexts,
        private readonly array $scanBoundedContexts
    ) {
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
        if (!empty($this->scanBoundedContexts['search_path'])
            && !empty($this->scanBoundedContexts['search_namespace'])
            && is_dir($this->scanBoundedContexts['search_path'])
        ) {
            $paths = Finder::create()
                ->in($this->scanBoundedContexts['search_path'])
                ->depth(0)
                ->directories();
            $namespace = new EntityNamespace($this->scanBoundedContexts['search_namespace']);
            foreach ($paths as $path) {
                $contextId = new BoundedContextId(strtolower($path->getBasename()));
                $pathNamespace = $namespace->getChildNamespace($path->getBasename());
                $resourceNamespace = $pathNamespace->getChildNamespace('Resources');
                $classes = $resourceNamespace->getClasses($path . '/Resources');
                $methodNamespace = $pathNamespace->getChildNamespace('Actions');
                $methods = $methodNamespace->getMethods($path . '/Actions');
                $result[$contextId->toNative()] = new BoundedContext(
                    $contextId,
                    $classes,
                    $methods
                );
            }
        }
        return new BoundedContextHashmap($result);
    }
}
