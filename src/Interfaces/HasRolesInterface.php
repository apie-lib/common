<?php

namespace Apie\Common\Interfaces;

use Apie\Core\Entities\EntityInterface;
use Apie\Core\Lists\StringList;

/**
 * If an entity has this interface and is used as the 'authenticated' user it can have roles.
 */
interface HasRolesInterface extends EntityInterface
{
    public function getRoles(): StringList;
}
