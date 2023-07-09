<?php
namespace Apie\Common\Wrappers;

use Apie\Common\Interfaces\UserDecorator;
use Apie\Core\Entities\EntityInterface;

/**
 * @template T of EntityInterface
 * @implements UserDecorator<T>
 */
abstract class AbstractApieUserDecorator implements UserDecorator
{
    /**
     * @param ApieUserDecoratorIdentifier<T> $id
     * @param T $entity
     */
    public function __construct(protected readonly ApieUserDecoratorIdentifier $id, protected readonly EntityInterface $entity)
    {
    }

    /**
     * @return T
     */
    public function getEntity(): EntityInterface
    {
        return $this->entity;
    }
}
