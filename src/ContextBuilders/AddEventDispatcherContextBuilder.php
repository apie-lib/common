<?php
namespace Apie\Common\ContextBuilders;

use Apie\Core\Context\ApieContext;
use Apie\Core\ContextBuilders\ContextBuilderInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class AddEventDispatcherContextBuilder implements ContextBuilderInterface
{
    public function __construct(private readonly EventDispatcherInterface $dispatcher)
    {
    }
    public function process(ApieContext $context): ApieContext
    {
        return $context->registerInstance($this->dispatcher);
    }
}
