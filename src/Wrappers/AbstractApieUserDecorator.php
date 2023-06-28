<?php
namespace Apie\Common\Wrappers;

use Apie\Common\Interfaces\UserDecorator;
use Apie\Core\Entities\EntityInterface;

abstract class AbstractApieUserDecorator implements UserDecorator
{
    /**
     * @param ApieUserDecoratorIdentifier<T> $id
     * @param T $entity
     */
    public function __construct(protected readonly ApieUserDecoratorIdentifier $id, protected readonly EntityInterface $entity)
    {
    }

    public function getEntity(): EntityInterface
    {
        return $this->entity;
    }
}
