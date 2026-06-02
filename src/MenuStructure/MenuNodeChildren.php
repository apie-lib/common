<?php
namespace Apie\Common\MenuStructure;

use Apie\Core\Lists\ItemHashmap;

class MenuNodeChildren extends ItemHashmap
{
    public function offsetGet(mixed $offset): MenuNode
    {
        return parent::offsetGet($offset);
    }
}
