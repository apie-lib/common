<?php
namespace Apie\Common\Interfaces;

use Apie\Core\BoundedContext\BoundedContext;

interface BoundedContextSelection
{
    public function getBoundedContextFromRequest(): ?BoundedContext;

    public function getBoundedContextFromClassName(string $className): ?BoundedContext;
}
