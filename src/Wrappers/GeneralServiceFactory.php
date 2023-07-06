<?php
namespace Apie\Common\Wrappers;

use Apie\Common\ContextBuilderFactory as CommonContextBuilderFactory;
use Apie\Common\ContextBuilders\BoundedContextProviderContextBuilder;
use Apie\Common\Interfaces\RouteDefinitionProviderInterface;
use Apie\Common\RouteDefinitions\ChainedRouteDefinitionsProvider;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\ContextBuilders\ContextBuilderFactory;
use Apie\Core\ContextBuilders\ContextBuilderInterface;
use Apie\Core\Datalayers\Grouped\DataLayerByBoundedContext;
use Apie\Core\Datalayers\Grouped\DataLayerByClass;
use Apie\Faker\ApieObjectFaker;
use Apie\Faker\Interfaces\ApieClassFaker;
use Apie\Serializer\DecoderHashmap;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\Argument\ServiceLocator;

/**
 * This is basically a work around around !tagged_iterators support with variadic arguments.
 */
final class GeneralServiceFactory
{
    private function __construct()
    {
    }

    /**
     * @param iterable<int, ContextBuilderInterface> $contextBuilders
     */
    public static function createContextBuilderFactory(
        BoundedContextHashmap $boundedContextHashmap,
        ?DecoderHashmap $decoderHashmap,
        iterable $contextBuilders
    ): ContextBuilderFactory {
        if ($decoderHashmap) {
            return CommonContextBuilderFactory::create(
                $boundedContextHashmap,
                $decoderHashmap,
                ...$contextBuilders
            );
        }
        return new ContextBuilderFactory(
            new BoundedContextProviderContextBuilder($boundedContextHashmap),
            ...$contextBuilders
        );
    }

    /**
     * @param iterable<int, RouteDefinitionProviderInterface> $routeDefinitionProviders
     */
    public static function createRoutedDefinitionProvider(iterable $routeDefinitionProviders): RouteDefinitionProviderInterface
    {
        return new ChainedRouteDefinitionsProvider(...$routeDefinitionProviders);
    }

    /**
     * @param array<string, mixed> $dataLayerConfig
     */
    public static function createDataLayerMap(
        array $dataLayerConfig,
        ServiceLocator $serviceLocator
    ): DataLayerByBoundedContext {
        $hashmap = new DataLayerByBoundedContext([]);
        foreach (($dataLayerConfig['context_mapping'] ?? []) as $boundedContextId => $config) {
            $map = new DataLayerByClass();
            foreach (($config['entity_mapping'] ?? []) as $entityClass => $serviceId) {
                $map[$entityClass] = $serviceLocator->get($serviceId);
            }
            $defaultServiceId = $config['default_datalayer'] ?? $dataLayerConfig['default_datalayer'] ?? RequestAwareInMemoryDatalayer::class;
            $map->setDefaultDataLayer(
                $serviceLocator->get($defaultServiceId)
            );
            $hashmap[$boundedContextId] = $map;
        }
        $hashmap->setDefaultDataLayer(
            $serviceLocator->get($dataLayerConfig['default_datalayer'] ?? RequestAwareInMemoryDatalayer::class)
        );
        return $hashmap;
    }

    /**
     * @param iterable<int, ApieClassFaker<object>> $fakeProviders
     */
    public static function createFaker(iterable $fakeProviders): Generator
    {
        $faker = Factory::create();
        $faker->addProvider(ApieObjectFaker::createWithDefaultFakers($faker, ...$fakeProviders));
    
        return $faker;
    }
}
