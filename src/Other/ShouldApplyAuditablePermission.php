<?php
namespace Apie\Common\Other;

use Apie\Core\Attributes\ApieContextAttribute;
use Apie\Core\Attributes\Auditable;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\Context\ApieContext;

/**
 * Checks if the user has permission to view the audit log based on the #[Auditable] attribute
 * of the audited entity.
 */
final class ShouldApplyAuditablePermission implements ApieContextAttribute
{
    public function applies(ApieContext $context): bool
    {
        $auditLog = $context->getContext(AuditLog::class, false);
        if (!$auditLog instanceof AuditLog) {
            return true;
        }

        $reference = $auditLog->getReference();
        $boundedContextId = $reference->getBoundedContextId();
        $entityClassName = $reference->getEntityClass()->toNative();

        $hashmap = $context->getContext(BoundedContextHashmap::class, false);
        if (!$hashmap) {
            return true;
        }

        $boundedContext = $hashmap[$boundedContextId->toNative()] ?? null;
        if (!$boundedContext) {
            return true;
        }

        foreach ($boundedContext->resources as $resource) {
            if ($resource->getShortName() === $entityClassName || $resource->name === $entityClassName) {
                foreach ($resource->getAttributes(Auditable::class) as $attribute) {
                    $auditable = $attribute->newInstance();
                    return $auditable->permission->applies($context);
                }
            }
        }

        return true;
    }
}
