<?php
namespace Apie\Common\Events;

use Apie\Common\Other\AuditLog;
use Apie\Core\Attributes\Auditable;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\ContextConstants;
use Apie\Core\Datalayers\ApieDatalayer;
use Apie\Core\ValueObjects\EntityReference;
use Apie\Serializer\PropertySerializer\PropertySerializer;
use ReflectionClass;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddAuditLog implements EventSubscriberInterface
{
    public function __construct(
        private readonly ApieDatalayer $datalayer,
        private readonly PropertySerializer $propertySerializer,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [ApieResourceCreated::class => 'onApieResourceCreated'];
    }

    public function onApieResourceCreated(ApieResourceCreated $event): void
    {
        foreach ((new ReflectionClass($event->resource))->getAttributes(Auditable::class) as $auditable) {
            $reference = EntityReference::createFromContext($event->context);

            if ($reference instanceof EntityReference) {
                $auditLog = new AuditLog(
                    $reference,
                    $this->propertySerializer->toJson($event->resource)
                );
                $this->datalayer->persistNew(
                    $auditLog,
                    new BoundedContextId($event->context->getContext(ContextConstants::BOUNDED_CONTEXT_ID))
                );
            }
        }
    }
}
