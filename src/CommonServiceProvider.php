<?php
namespace Apie\Common;

use Apie\ServiceProviderGenerator\UseGeneratedMethods;
use Illuminate\Support\ServiceProvider;

/**
 * This file is generated with apie/service-provider-generator from file: common.yaml
 * @codecoverageIgnore
 */
class CommonServiceProvider extends ServiceProvider
{
    use UseGeneratedMethods;

    public function register()
    {
        $this->app->singleton(
            \Apie\Common\ActionDefinitionProvider::class,
            function ($app) {
                return new \Apie\Common\ActionDefinitionProvider(
                
                );
            }
        );
        $this->app->singleton(
            \Apie\Common\ApieFacade::class,
            function ($app) {
                return new \Apie\Common\ApieFacade(
                    $app->make('apie.route_definitions.provider'),
                    $app->make(\Apie\Core\BoundedContext\BoundedContextHashmap::class),
                    $app->make(\Apie\Serializer\Serializer::class),
                    $app->make(\Apie\Core\Datalayers\ApieDatalayer::class)
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\Common\ApieFacade::class,
            array(
                0 => 'apie.context',
            )
        );
        $this->app->tag([\Apie\Common\ApieFacade::class], 'apie.context');
        $this->app->singleton(
            \Apie\Common\RouteDefinitions\PossibleRoutePrefixProvider::class,
            function ($app) {
                return new \Apie\Common\RouteDefinitions\PossibleRoutePrefixProvider(
                    $this->parseArgument('%apie.cms.base_url%'),
                    $this->parseArgument('%apie.rest_api.base_url%')
                );
            }
        );
        $this->app->singleton(
            \Apie\Common\RequestBodyDecoder::class,
            function ($app) {
                return new \Apie\Common\RequestBodyDecoder(
                    $app->make(\Apie\Serializer\DecoderHashmap::class)
                );
            }
        );
        $this->app->singleton(
            'apie.bounded_context.hashmap_factory',
            function ($app) {
                return new \Apie\Common\Wrappers\BoundedContextHashmapFactory(
                    $this->parseArgument('%apie.bounded_contexts%')
                );
            }
        );
        $this->app->bind(\Apie\Common\Interfaces\RouteDefinitionProviderInterface::class, 'apie.route_definitions.provider');
        
        $this->app->singleton(
            'apie.route_definitions.provider',
            function ($app) {
                return \Apie\Common\Wrappers\GeneralServiceFactory::createRoutedDefinitionProvider(
                    $this->getTaggedServicesIterator('apie.common.route_definition')
                );
                
            }
        );
        $this->app->singleton(
            \Apie\Common\ErrorHandler\ApiErrorRenderer::class,
            function ($app) {
                return new \Apie\Common\ErrorHandler\ApiErrorRenderer(
                
                );
            }
        );
        $this->app->singleton(
            \Apie\Common\ContextBuilders\ServiceContextBuilder::class,
            function ($app) {
                return new \Apie\Common\ContextBuilders\ServiceContextBuilder(
                    $this->getTaggedServicesServiceLocator('apie.context')
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\Common\ContextBuilders\ServiceContextBuilder::class,
            array(
              0 => 'apie.core.context_builder',
            )
        );
        $this->app->tag([\Apie\Common\ContextBuilders\ServiceContextBuilder::class], 'apie.core.context_builder');
        $this->app->singleton(
            \Apie\Common\Wrappers\RequestAwareInMemoryDatalayer::class,
            function ($app) {
                return new \Apie\Common\Wrappers\RequestAwareInMemoryDatalayer(
                    $app->make(\Apie\Common\Interfaces\BoundedContextSelection::class),
                    $app->make(\Apie\Core\Datalayers\Search\LazyLoadedListFilterer::class)
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\Common\Wrappers\RequestAwareInMemoryDatalayer::class,
            array(
              0 => 'apie.datalayer',
            )
        );
        $this->app->tag([\Apie\Common\Wrappers\RequestAwareInMemoryDatalayer::class], 'apie.datalayer');
        $this->app->bind('apie', \Apie\Common\ApieFacade::class);
        
        
    }
}
