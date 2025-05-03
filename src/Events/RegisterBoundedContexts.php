<?php
namespace Apie\Common\Events;

use Apie\Core\BoundedContext\BoundedContextHashmap;

final class RegisterBoundedContexts
{
    public function __construct(public readonly BoundedContextHashmap $hashmap)
    {
    }
}
