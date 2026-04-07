<?php
namespace Apie\Common\Other;

use Apie\Common\Enums\AccessDenied;
use Apie\Core\Attributes\ApieContextAttribute;
use Apie\Serializer\Context\ApieSerializerContext;

/**
 * Represents a scalar value of an entity snapshot.
 */
class EntitySnapshotLeaf implements EntitySnapshotInstance
{
    public function __construct(
        private readonly string|int|float|bool|null $scalar,
        public readonly ApieContextAttribute $context
    ) {
    }

    public function applies(ApieSerializerContext $apieSerializerContext): bool
    {
        return $this->context->applies($apieSerializerContext->getContext());
    }

    public function normalize(ApieSerializerContext $apieSerializerContext): string|int|float|bool|null|AccessDenied
    {
        if (!$this->applies($apieSerializerContext)) {
            return AccessDenied::Denied;
        }
        return $this->scalar;
    }
}
