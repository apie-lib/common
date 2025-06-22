<?php
namespace Apie\Common\ContextBuilders;

use Apie\Core\Context\ApieContext;
use Apie\Core\ContextBuilders\ContextBuilderInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;

final class AddLockManagerContextBuilder implements ContextBuilderInterface
{
    public function __construct(
        private readonly LockFactory $lockFactory = new LockFactory(new FlockStore())
    ) {
    }

    public function process(ApieContext $context): ApieContext
    {
        return $context->withContext(
            LockFactory::class,
            $this->lockFactory
        );
    }
}
