<?php
namespace Apie\Common\Lists;

use Apie\Common\Enums\UrlPrefix;
use Apie\Core\Lists\ItemList;

class UrlPrefixList extends ItemList
{
    public function offsetGet(mixed $offset): UrlPrefix
    {
        return parent::offsetGet($offset);
    }
}
