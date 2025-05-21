<?php
namespace Apie\Common\ContextBuilders;

use Apie\Core\Context\ApieContext;
use Apie\Core\ContextBuilders\ContextBuilderInterface;
use Apie\Core\ContextConstants;

final class FrameworkContextBuilder implements ContextBuilderInterface
{
    public function __construct(
        private readonly string $frameworkName
    ) {
    }

    public function process(ApieContext $context): ApieContext
    {
        return $context->withContext(
            ContextConstants::FRAMEWORK,
            $this->frameworkName
        );
    }
}
