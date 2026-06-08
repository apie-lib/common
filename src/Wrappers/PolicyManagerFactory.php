<?php
namespace Apie\Common\Wrappers;

use Apie\Common\Config\Configuration;
use Apie\Common\ValueObjects\EntityNamespace;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Policies\BoundedContextPolicyProvider;
use Apie\Core\Policies\FallbackPolicy;
use Apie\Core\Policies\PolicyManager;
use Apie\Core\Policies\PolicyProviderHashmap;
use Apie\Core\Policies\ResourcePolicyProvider;
use Psr\Container\ContainerInterface as ContainerContainerInterface;
use Symfony\Component\Finder\Finder;

/**
 * Creates a PolicyManager instance from the Apie configuration.
 *
 * @see Configuration in apie/apie-bundle for Symfony
 * @see resources/apie.php in apie/laravel-apie for Laravel
 */
final class PolicyManagerFactory
{
    /**
     * @param array<string, mixed> $boundedContexts
     * @param array<string, string> $scanBoundedContexts
     */
    public function __construct(
        private readonly array $boundedContexts,
        private readonly array $scanBoundedContexts,
        private readonly bool $defaultAllowIfNoPolicy,
        private readonly ContainerContainerInterface $container
    ) {
    }

    private function createResourcePolicyProvider(EntityNamespace $namespace, string $folder): ResourcePolicyProvider
    {
        $classes = $namespace->getClasses($folder);
        $classPolicies = [];
        foreach ($classes as $class) {
            $shortName = $class->getShortName();
            if (str_ends_with($shortName, 'Policy')) {
                $shortName = substr($shortName, 0, -6);
            }
            $classPolicies[$shortName] = $this->container->has($class->name)
                ? $this->container->get($class->name)
                : $class->newInstance();
        }
        return new ResourcePolicyProvider(
            new ItemHashmap($classPolicies),
            new FallbackPolicy()
        );
    }

    private function createPolicyForBoundedContexts(): BoundedContextPolicyProvider
    {
        $blocks = [];
        foreach ($this->boundedContexts as $boundedContextId => $boundedContextConfig) {
            $contextId = new BoundedContextId($boundedContextId);
            $namespace = new EntityNamespace($boundedContextConfig['policies_namespace']);
            $blocks[$contextId->toNative()] = $this->createResourcePolicyProvider($namespace, $boundedContextConfig['policies_folder']);
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
                $namespace = $pathNamespace->getChildNamespace('Policies');
                $blocks[$contextId->toNative()] = $this->createResourcePolicyProvider($namespace, $path . '/Policies');
            }
        }
        return new BoundedContextPolicyProvider(
            new PolicyProviderHashmap($blocks),
            new FallbackPolicy()
        );
    }

    public function create(): PolicyManager
    {
        return new PolicyManager($this->createPolicyForBoundedContexts(), $this->defaultAllowIfNoPolicy);
    }
}
