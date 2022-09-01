<?php
namespace Apie\Common\RouteDefinitions;

use Apie\Common\Interfaces\HasRouteDefinition;
use Apie\Core\Lists\ItemHashmap;

final class ActionHashmap extends ItemHashmap
{
    public function offsetGet(mixed $offset): HasRouteDefinition
    {
        return parent::offsetGet($offset);
    }

    /**
     * @TODO: merge actions with same route definition....
     */
    public function merge(self $hashmap): self
    {
        return new self($this->internalArray + $hashmap->internalArray);
    }
}
