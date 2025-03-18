<?php
namespace Apie\Common\Wrappers;

use Apie\Common\Interfaces\UserDecorator;
use Apie\Common\ValueObjects\DecryptedAuthenticatedUser;
use Apie\Core\Entities\EntityInterface;

/**
 * @template T of EntityInterface
 * @implements UserDecorator<T>
 */
abstract class AbstractApieUserDecorator implements UserDecorator
{
    /**
     * @param DecryptedAuthenticatedUser<T> $id
     * @param T $entity
     */
    public function __construct(
        protected readonly DecryptedAuthenticatedUser $id,
        protected readonly EntityInterface $entity
    ) {
    }

    /**
     * @return T
     */
    public function getEntity(): EntityInterface
    {
        return $this->entity;
    }
}
