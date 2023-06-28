<?php
namespace Apie\Common\Interfaces;

use Apie\Core\Entities\EntityInterface;

/**
 * @template T of EntityInterface
 */
interface UserDecorator
{
    /**
     * @return EntityInterface<T>
     */
    public function getEntity(): EntityInterface;
}
