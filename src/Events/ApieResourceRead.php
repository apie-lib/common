<?php
namespace Apie\Common\Events;

use Apie\Core\Context\ApieContext;
use Apie\Core\Entities\EntityInterface;

class ApieResourceRead
{
    public function __construct(
        public readonly EntityInterface $resource,
        public readonly ApieContext $context
    ) {
    }
}
