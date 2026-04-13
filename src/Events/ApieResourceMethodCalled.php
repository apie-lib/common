<?php
namespace Apie\Common\Events;

use Apie\Core\Context\ApieContext;
use Apie\Core\Entities\EntityInterface;

final class ApieResourceMethodCalled
{
    public function __construct(
        public readonly EntityInterface $resource,
        public readonly string $methodName,
        public readonly ApieContext $context
    ) {
    }
}
