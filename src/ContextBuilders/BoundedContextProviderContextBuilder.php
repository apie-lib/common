<?php
namespace Apie\Common\ContextBuilders;

use Apie\Common\ContextConstants;
use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextBuilders\ContextBuilderInterface;

class BoundedContextProviderContextBuilder implements ContextBuilderInterface
{
    public function __construct(private readonly BoundedContextHashmap $boundedContextHashmap)
    {
    }

    public function process(ApieContext $context): ApieContext
    {
        $context = $context->registerInstance($this->boundedContextHashmap);
        if ($context->hasContext(ContextConstants::BOUNDED_CONTEXT_ID) && !$context->hasContext(BoundedContext::class)) {
            $id = $context->getContext(ContextConstants::BOUNDED_CONTEXT_ID);
            if (isset($this->boundedContextHashmap[$id])) {
                return $context->withContext(BoundedContext::class, $this->boundedContextHashmap[$id]);
            }
        }

        return $context;
    }
}
