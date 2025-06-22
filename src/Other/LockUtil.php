<?php
namespace Apie\Common\Other;

use Apie\Core\Context\ApieContext;
use Apie\Core\Exceptions\LockException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\SharedLockInterface;

final class LockUtil
{
    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * @param array<int, string> $fields
     */
    public static function createLock(ApieContext $context, array $fields, string $prefix = 'entity', bool $write = false): SharedLockInterface
    {
        $lockFactory = $context->getContext(LockFactory::class);
        assert($lockFactory instanceof LockFactory);
        $invariables = [];
        foreach ($fields as $field) {
            $invariables[$field] = $context->getContext($field, false);
        }
        ksort($invariables);
        $lock = $lockFactory->createLock($prefix . md5(json_encode($invariables)));
        $method = $write ? 'acquire' : 'acquireRead';
        if (!$lock->$method(true)) {
            throw new LockException();
        }
        
        return $lock;
    }
}
