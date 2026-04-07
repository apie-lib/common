<?php
namespace Apie\Common\Other;

use Apie\Common\Enums\AccessDenied;
use Apie\Core\Attributes\ApieContextAttribute;
use Apie\Core\FileStorage\FileStorageInterface;
use Apie\Core\FileStorage\StoredFile;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Lists\SerializedHashmap;

/**
 * Represents an uploaded file in the audit log.
 */
class EntitySnapshotFile implements EntitySnapshotInstance
{
    public function __construct(
        private readonly ?string $storagePath,
        private readonly string $originalFilename,
        public readonly ApieContextAttribute $context
    ) {
    }

    public function applies(ApieSerializerContext $apieSerializerContext): bool
    {
        return $this->context->applies($apieSerializerContext->getContext());
    }

    public function normalize(ApieSerializerContext $apieSerializerContext): SerializedHashmap|AccessDenied
    {
        if (!$this->applies($apieSerializerContext)) {
            return AccessDenied::Denied;
        }
        $storage = $apieSerializerContext->getContext()->getContext(FileStorageInterface::class, false);
        $file = ($storage instanceof FileStorageInterface)
            ? StoredFile::createFromStorage($storage, $this->storagePath)
            : null;
        return new SerializedHashmap([
            'storagePath' => $this->storagePath,
            'originalFilename' => $this->originalFilename,
            'file' => $file,
        ]);
    }
}
