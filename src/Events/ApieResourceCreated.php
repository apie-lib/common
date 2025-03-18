<?php
namespace Apie\Common\Events;

use Apie\Core\Entities\EntityInterface;

final class ApieResourceCreated
{
    public function __construct(
        public readonly EntityInterface $resource
    ) {
    }
}
