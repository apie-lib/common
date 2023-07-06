<?php
namespace Apie\Common;

use Apie\ServiceProviderGenerator\UseGeneratedMethods;
use Illuminate\Support\ServiceProvider;

/**
 * This file is generated with apie/service-provider-generator from file: common.yaml
 * @codecoverageIgnore
 * @phpstan-ignore
 */
class CommonServiceProvider extends ServiceProvider
{
    use UseGeneratedMethods;

    public function register()
    {
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
        $this->app->singleton(
            \Apie\Common\RequestBodyDecoder::class,
            function ($app) {
                return new \Apie\Common\RequestBodyDecoder(
                    $app->make(\Apie\Serializer\DecoderHashmap::class),
                    $app->make(\Apie\Core\Session\CsrfTokenProvider::class)
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
        $this->app->singleton(
            \Apie\Common\Wrappers\RequestAwareInMemoryDatalayer::class,
            function ($app) {
                return new \Apie\Common\Wrappers\RequestAwareInMemoryDatalayer(
                    $app->make(\Apie\Common\Interfaces\BoundedContextSelection::class)
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
