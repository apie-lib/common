<?php
namespace Apie\Common;

use Apie\ServiceProviderGenerator\UseGeneratedMethods;
use Illuminate\Support\ServiceProvider;

/**
 * This file is generated with apie/service-provider-generator from file: add_basic_auth.yaml
 * @codeCoverageIgnore
 */
class AddBasicAuthServiceProvider extends ServiceProvider
{
    use UseGeneratedMethods;

    public function register()
    {
        $this->app->singleton(
            \Apie\Common\BasicAuth\AddBasicAuthContextBuilder::class,
            function ($app) {
                return new \Apie\Common\BasicAuth\AddBasicAuthContextBuilder(
                
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\Common\BasicAuth\AddBasicAuthContextBuilder::class,
            array(
              0 => 'apie.core.context_builder',
            )
        );
        $this->app->tag([\Apie\Common\BasicAuth\AddBasicAuthContextBuilder::class], 'apie.core.context_builder');
        $this->app->singleton(
            \Apie\Common\BasicAuth\AddBasicAuthToOpenApiSubscriber::class,
            function ($app) {
                return new \Apie\Common\BasicAuth\AddBasicAuthToOpenApiSubscriber(
                
                );
            }
        );
        \Apie\ServiceProviderGenerator\TagMap::register(
            $this->app,
            \Apie\Common\BasicAuth\AddBasicAuthToOpenApiSubscriber::class,
            array(
              0 => 'kernel.event_subscriber',
            )
        );
        $this->app->tag([\Apie\Common\BasicAuth\AddBasicAuthToOpenApiSubscriber::class], 'kernel.event_subscriber');
        
    }
}
