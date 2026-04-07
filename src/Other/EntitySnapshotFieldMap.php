<?php
namespace Apie\Common\Other;

use Apie\Core\Lists\ItemHashmap;

class EntitySnapshotFieldMap extends ItemHashmap
{
    public function offsetGet(mixed $offset): EntitySnapshotInstance
    {
        return parent::offsetGet($offset);
    }
}
