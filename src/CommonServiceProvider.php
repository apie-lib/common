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
        $this->registerSingleton(
            \Apie\Common\ActionDefinitionProvider::class,
            function ($app) {
                return new \Apie\Common\ActionDefinitionProvider(
                    $this->getTaggedServicesServiceLocator('apie.context')
                );
            }
        );
        $this->registerSingleton(
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
        $this->registerSingleton(
            \Apie\Common\ContextBuilders\AddTextEncrypterContextBuilder::class,
            function ($app) {
                return new \Apie\Common\ContextBuilders\AddTextEncrypterContextBuilder(
                    $app->make(\Psr\Cache\CacheItemPoolInterface::class),
                    $app->make(\Psr\Log\LoggerInterface::class),
                    $this->parseArgument('%apie.encryption_key%', \Apie\Common\ContextBuilders\AddTextEncrypterContextBuilder::class, 2)
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
        $this->registerSingleton(
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
        $this->registerSingleton(
            \Apie\Common\Command\ApieAuditLogForMigrationCommand::class,
            function ($app) {
                return new \Apie\Common\Command\ApieAuditLogForMigrationCommand(
                    $app->make(\Apie\Core\BoundedContext\BoundedContextHashmap::class),
                    $app->make(\Apie\Core\Datalayers\ApieDatalayer::class),
                    $app->make(\Apie\Core\ContextBuilders\ContextBuilderFactory::class)
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\Common\Command\ApieAuditLogForMigrationCommand::class,
            array(
              0 => 'console.command',
            )
        );
        $this->app->tag([\Apie\Common\Command\ApieAuditLogForMigrationCommand::class], 'console.command');
        $this->registerSingleton(
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
        $this->registerSingleton(
            \Apie\Common\LoginService::class,
            function ($app) {
                return new \Apie\Common\LoginService(
                    $app->make(\Apie\Core\BoundedContext\BoundedContextHashmap::class),
                    $app->make(\Apie\Common\ActionDefinitionProvider::class),
                    $app->make(\Apie\Serializer\Serializer::class)
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\Common\LoginService::class,
            array(
              0 => 'apie.context',
            )
        );
        $this->app->tag([\Apie\Common\LoginService::class], 'apie.context');
        $this->registerSingleton(
            \Apie\Common\Events\ResponseDispatcher::class,
            function ($app) {
                return new \Apie\Common\Events\ResponseDispatcher(
                    $app->make(\Psr\EventDispatcher\EventDispatcherInterface::class)
                );
            }
        );
        $this->registerSingleton(
            \Apie\Common\RouteDefinitions\PossibleRoutePrefixProvider::class,
            function ($app) {
                return new \Apie\Common\RouteDefinitions\PossibleRoutePrefixProvider(
                    $this->parseArgument('%apie.cms.base_url%', \Apie\Common\RouteDefinitions\PossibleRoutePrefixProvider::class, 0),
                    $this->parseArgument('%apie.rest_api.base_url%', \Apie\Common\RouteDefinitions\PossibleRoutePrefixProvider::class, 1)
                );
            }
        );
        $this->registerSingleton(
            \Apie\Common\RequestBodyDecoder::class,
            function ($app) {
                return new \Apie\Common\RequestBodyDecoder(
                    $app->make(\Apie\Serializer\DecoderHashmap::class)
                );
            }
        );
        $this->registerSingleton(
            \Apie\Common\Events\AddAuditLog::class,
            function ($app) {
                return new \Apie\Common\Events\AddAuditLog(
                    $app->make(\Apie\Core\Datalayers\ApieDatalayer::class)
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\Common\Events\AddAuditLog::class,
            array(
              0 => 'kernel.event_subscriber',
            )
        );
        $this->app->tag([\Apie\Common\Events\AddAuditLog::class], 'kernel.event_subscriber');
        $this->registerSingleton(
            'apie.bounded_context.hashmap_factory',
            function ($app) {
                return new \Apie\Common\Wrappers\BoundedContextHashmapFactory(
                    $this->parseArgument('%apie.bounded_contexts%', \Apie\Common\Wrappers\BoundedContextHashmapFactory::class, 0),
                    $this->parseArgument('%apie.scan_bounded_contexts%', \Apie\Common\Wrappers\BoundedContextHashmapFactory::class, 1),
                    $app->make(\Psr\EventDispatcher\EventDispatcherInterface::class)
                );
            }
        );
        $this->registerSingleton(
            \Apie\Common\Wrappers\PolicyManagerFactory::class,
            function ($app) {
                return new \Apie\Common\Wrappers\PolicyManagerFactory(
                    $this->parseArgument('%apie.bounded_contexts%', \Apie\Common\Wrappers\PolicyManagerFactory::class, 0),
                    $this->parseArgument('%apie.scan_bounded_contexts%', \Apie\Common\Wrappers\PolicyManagerFactory::class, 1),
                    false,
                    $app
                );
            }
        );
        $this->registerSingleton(
            \Apie\Core\Policies\PolicyManager::class,
            function ($app) {
                return $this->app->make(\Apie\Common\Wrappers\PolicyManagerFactory::class)->create(
                
                );
                
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\Core\Policies\PolicyManager::class,
            array(
              0 => 'apie.context',
            )
        );
        $this->app->tag([\Apie\Core\Policies\PolicyManager::class], 'apie.context');
        $this->app->bind(\Apie\Common\Interfaces\RouteDefinitionProviderInterface::class, 'apie.route_definitions.provider');
        
        $this->registerSingleton(
            'apie.route_definitions.provider',
            function ($app) {
                return \Apie\Common\Wrappers\GeneralServiceFactory::createRoutedDefinitionProvider(
                    $this->getTaggedServicesIterator('apie.common.route_definition')
                );
                
            }
        );
        $this->registerSingleton(
            \Apie\Common\ErrorHandler\ApiErrorRenderer::class,
            function ($app) {
                return new \Apie\Common\ErrorHandler\ApiErrorRenderer(
                
                );
            }
        );
        $this->registerSingleton(
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
        $this->registerSingleton(
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
        $this->registerSingleton(
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
        $this->registerSingleton(
            \Apie\Common\ContextBuilders\AddLockManagerContextBuilder::class,
            function ($app) {
                return new \Apie\Common\ContextBuilders\AddLockManagerContextBuilder(
                    $app->make(\Symfony\Component\Lock\LockFactory::class)
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\Common\ContextBuilders\AddLockManagerContextBuilder::class,
            array(
              0 => 'apie.core.context_builder',
            )
        );
        $this->app->tag([\Apie\Common\ContextBuilders\AddLockManagerContextBuilder::class], 'apie.core.context_builder');
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
              1 => 'always-singleton',
            )
        );
        $this->app->tag([\Apie\Common\Wrappers\RequestAwareInMemoryDatalayer::class], 'apie.datalayer');
        $this->app->tag([\Apie\Common\Wrappers\RequestAwareInMemoryDatalayer::class], 'always-singleton');
        $this->app->bind('apie', \Apie\Common\ApieFacade::class);
        
        
    }
}
