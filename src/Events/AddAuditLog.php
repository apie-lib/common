<?php
namespace Apie\Common\Events;

use Apie\Common\Other\AuditLog;
use Apie\Core\Attributes\Auditable;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\ContextConstants;
use Apie\Core\Datalayers\ApieDatalayer;
use Apie\Core\ValueObjects\IdFriendlyEntityReference;
use Apie\Serializer\ValueObjects\SerializedPhpObject;
use ReflectionClass;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddAuditLog implements EventSubscriberInterface
{
    public function __construct(
        private readonly ApieDatalayer $datalayer
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [ApieResourceCreated::class => 'onApieResourceCreated'];
    }

    public function onApieResourceCreated(ApieResourceCreated $event): void
    {
        foreach ((new ReflectionClass($event->resource))->getAttributes(Auditable::class) as $auditable) {
            $reference = IdFriendlyEntityReference::createFromContext($event->context);

            if ($reference instanceof IdFriendlyEntityReference) {
                $auditLog = new AuditLog(
                    $reference,
                    SerializedPhpObject::createFromPhpObject($event->resource)
                );
                $this->datalayer->persistNew(
                    $auditLog,
                    new BoundedContextId($event->context->getContext(ContextConstants::BOUNDED_CONTEXT_ID))
                );
            }
        }
    }
}
