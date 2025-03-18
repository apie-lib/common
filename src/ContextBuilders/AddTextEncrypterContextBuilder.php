<?php
namespace Apie\Common\ContextBuilders;

use Apie\Common\Wrappers\TextEncrypter;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextBuilders\ContextBuilderInterface;
use Apie\Core\Identifiers\UuidV4;
use Psr\Cache\CacheItemPoolInterface;
use SensitiveParameter;

class AddTextEncrypterContextBuilder implements ContextBuilderInterface
{
    public function __construct(
        private CacheItemPoolInterface $cache,
        #[SensitiveParameter] private ?string $encryptionKey = null
    ) {
        if ($encryptionKey === null) {
            $this->encryptionKey = $this->cache->getItem('apie.encryption_key')->get();
            if (!$this->encryptionKey) {
                $this->encryptionKey = UuidV4::createRandom()->toNative();
                $this->cache->save(
                    $this->cache->getItem('apie.encryption_key')->set($this->encryptionKey)
                );
            }
        }
    }

    public function process(ApieContext $context): ApieContext
    {
        return $context
            ->withContext(TextEncrypter::class, new TextEncrypter($this->encryptionKey));
    }
}
