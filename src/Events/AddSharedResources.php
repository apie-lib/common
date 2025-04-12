<?php
namespace Apie\Common\Events;

use Apie\Core\BackgroundProcess\SequentialBackgroundProcess;
use Apie\Core\BoundedContext\BoundedContext;
use ReflectionClass;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddSharedResources implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [RegisterBoundedContexts::class => 'addSharedResources'];
    }

    public function addSharedResources(RegisterBoundedContexts $registerBoundedContexts): void
    {
        foreach ($registerBoundedContexts->hashmap as $boundedContext) {
            /** @var BoundedContext $boundedContext */
            $lists = $boundedContext->findRelatedClasses()->toStringArray();
            if (in_array(SequentialBackgroundProcess::class, $lists)) {
                // @phpstan-ignore property.readOnlyAssignOutOfClass
                $boundedContext->resources[] = new ReflectionClass(SequentialBackgroundProcess::class);
            }
        }
    }
}
