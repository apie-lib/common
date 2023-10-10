<?php
namespace Apie\Common\Lists;

use Apie\Common\ActionDefinitions\ActionDefinitionInterface;
use Apie\Core\Lists\ItemList;

class ActionDefinitionList extends ItemList
{
    public function offsetGet(mixed $offset): ActionDefinitionInterface
    {
        return parent::offsetGet($offset);
    }
}
