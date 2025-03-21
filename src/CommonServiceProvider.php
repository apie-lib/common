<?php
namespace Apie\Common;

use Apie\ServiceProviderGenerator\UseGeneratedMethods;
use Illuminate\Support\ServiceProvider;

/**
 * This file is generated with apie/service-provider-generator from file: common.yaml
 * @codeCoverageIgnore
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
            \Apie\Common\ContextBuilders\AddEventDispatcherContextBuilder::class,
            function ($app) {
                return new \Apie\Common\ContextBuilders\AddEventDispatcherContextBuilder(
                    $app->make(\Psr\EventDispatcher\EventDispatcherInterface::class)
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\Common\ContextBuilders\AddEventDispatcherContextBuilder::class,
            array(
              0 => 'apie.core.context_builder',
            )
        );
        $this->app->tag([\Apie\Common\ContextBuilders\AddEventDispatcherContextBuilder::class], 'apie.core.context_builder');
        $this->app->singleton(
            \Apie\Common\ContextBuilders\AddTextEncrypterContextBuilder::class,
            function ($app) {
                return new \Apie\Common\ContextBuilders\AddTextEncrypterContextBuilder(
                    $app->make(\Psr\Cache\CacheItemPoolInterface::class),
                    $this->parseArgument('%apie.encryption_key%')
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\Common\ContextBuilders\AddTextEncrypterContextBuilder::class,
            array(
              0 => 'apie.core.context_builder',
            )
        );
        $this->app->tag([\Apie\Common\ContextBuilders\AddTextEncrypterContextBuilder::class], 'apie.core.context_builder');
        $this->app->singleton(
            \Apie\Common\Command\ApieUpdateRecalculatingCommand::class,
            function ($app) {
                return new \Apie\Common\Command\ApieUpdateRecalculatingCommand(
                    $app->make(\Apie\Core\BoundedContext\BoundedContextHashmap::class),
                    $app->make(\Apie\Core\Datalayers\ApieDatalayer::class),
                    $app->make(\Apie\Core\ContextBuilders\ContextBuilderFactory::class)
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\Common\Command\ApieUpdateRecalculatingCommand::class,
            array(
              0 => 'console.command',
            )
        );
        $this->app->tag([\Apie\Common\Command\ApieUpdateRecalculatingCommand::class], 'console.command');
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
            \Apie\Common\Events\ResponseDispatcher::class,
            function ($app) {
                return new \Apie\Common\Events\ResponseDispatcher(
                    $app->make(\Psr\EventDispatcher\EventDispatcherInterface::class)
                );
            }
        );
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
                    $this->parseArgument('%apie.bounded_contexts%'),
                    $this->parseArgument('%apie.scan_bounded_contexts%')
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
            \Apie\Common\ContextBuilders\CheckAuthenticatedContextBuilder::class,
            function ($app) {
                return new \Apie\Common\ContextBuilders\CheckAuthenticatedContextBuilder(
                    $app->make(\Psr\Log\LoggerInterface::class)
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\Common\ContextBuilders\CheckAuthenticatedContextBuilder::class,
            array(
              0 =>
              array(
                'name' => 'apie.core.context_builder',
                'priority' => -1,
              ),
            )
        );
        $this->app->tag([\Apie\Common\ContextBuilders\CheckAuthenticatedContextBuilder::class], 'apie.core.context_builder');
        $this->app->singleton(
            \Apie\Common\Events\AddAuthenticationCookie::class,
            function ($app) {
                return new \Apie\Common\Events\AddAuthenticationCookie(
                
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\Common\Events\AddAuthenticationCookie::class,
            array(
              0 => 'kernel.event_subscriber',
            )
        );
        $this->app->tag([\Apie\Common\Events\AddAuthenticationCookie::class], 'kernel.event_subscriber');
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
