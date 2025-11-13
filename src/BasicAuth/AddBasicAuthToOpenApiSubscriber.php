<?php
namespace Apie\Common\BasicAuth;

use Apie\RestApi\Events\OpenApiSchemaGeneratedEvent;
use cebe\openapi\spec\SecurityScheme;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddBasicAuthToOpenApiSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            OpenApiSchemaGeneratedEvent::class => 'onOpenApiSchemaGenerated'
        ];
    }

    public function onOpenApiSchemaGenerated(OpenApiSchemaGeneratedEvent $event): void
    {
        $openApi = $event->openApi;
        $securitySchemes = $openApi->components->securitySchemes ?? [];
        
        $securitySchemes['BasicAuth'] = new SecurityScheme([
            'type' => 'http',
            'scheme' => 'basic',
        ]);

        $openApi->components->securitySchemes = $securitySchemes;

        $security = $openApi->security ?? [];
        $security[] = ['BasicAuth' => []];
        $security[] = []; // add anonymous security option as well....
        $openApi->security = $security;
    }
}
