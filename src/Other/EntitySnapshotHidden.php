<?php
namespace Apie\Common\Other;

use Apie\Common\Enums\AccessDenied;
use Apie\Common\Enums\HiddenField;
use Apie\Core\Attributes\ApieContextAttribute;
use Apie\Serializer\Context\ApieSerializerContext;

/**
 * Audit log snapshot hidden field. Value is redacted (password, etc.)
 */
class EntitySnapshotHidden implements EntitySnapshotInstance
{
    public function __construct(
        private readonly ApieContextAttribute $context
    ) {
    }

    public function applies(ApieSerializerContext $apieSerializerContext): bool
    {
        return $this->context->applies($apieSerializerContext->getContext());
    }

    public function normalize(ApieSerializerContext $apieSerializerContext): HiddenField|AccessDenied
    {
        if (!$this->applies($apieSerializerContext)) {
            return AccessDenied::Denied;
        }
        return HiddenField::Hidden;
    }
}
