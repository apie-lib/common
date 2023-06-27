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
        $this->app->bind(\Apie\Common\ApieFacade::class, 'apie');
        
        
    }
}
