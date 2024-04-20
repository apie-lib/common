<?php
namespace Apie\Common\Events;

use Apie\Core\Context\ApieContext;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseDispatcher
{
    public function __construct(private readonly EventDispatcherInterface $dispatcher)
    {
    }

    public function triggerResponseCreated(ResponseInterface $response, ApieContext $context): ResponseInterface
    {
        $event = new ApieResponseCreated($response, $context);
        $this->dispatcher->dispatch($event);
        return $event->response;
    }
}
