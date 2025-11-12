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

        $openApi->components->securitySchemes['BasicAuth'] = new SecurityScheme([
            'type' => 'http',
            'scheme' => 'basic',
        ]);

        $openApi->security[] = ['BasicAuth' => []];
    }
}
