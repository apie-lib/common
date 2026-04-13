<?php
namespace Apie\Common\Events;

use Apie\Core\Context\ApieContext;
use Apie\Core\Datalayers\Lists\PaginatedResult;
use Apie\Core\Entities\EntityInterface;

class ApieResourceReadList
{
    /**
     * @param PaginatedResult<EntityInterface> $resource
     */
    public function __construct(
        public readonly PaginatedResult $resource,
        public readonly ApieContext $context
    ) {
    }
}
