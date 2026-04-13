<?php
namespace Apie\Common\Events;

use Apie\Common\Other\Audit\AuditCreate;
use Apie\Common\Other\Audit\AuditMethodCalled;
use Apie\Common\Other\Audit\AuditModified;
use Apie\Common\Other\Audit\AuditRead;
use Apie\Common\Other\Audit\AuditRemoved;
use Apie\Common\Other\AuditLog;
use Apie\Core\Attributes\Auditable;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Core\Datalayers\ApieDatalayer;
use Apie\Core\Enums\ConsoleCommand;
use Apie\Core\Enums\RequestMethod;
use Apie\Core\ValueObjects\IdFriendlyEntityReference;
use Apie\Core\ValueObjects\Utils;
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
        return [
            ApieResourceCreated::class      => 'onApieResourceCreated',
            ApieResourceRead::class         => 'onApieResourceRead',
            ApieResourceReadList::class     => 'onApieResourceReadList',
            ApieResourceModified::class     => 'OnApieResourceModified',
            ApieResourceRemoved::class      => 'onApieResourceRemoved',
            ApieResourceMethodCalled::class => 'onApieResourceMethodCalled'
        ];
    }

    private function createUser(ApieContext $context): ?SerializedPhpObject
    {
        $user = $context->getContext(ContextConstants::AUTHENTICATED_USER, false);
        if ($user === null) {
            if ($context->getContext(ConsoleCommand::class, false)) {
                return SerializedPhpObject::createFromPhpObject(ConsoleCommand::CONSOLE_COMMAND);
            }
            return null;
        }
        return SerializedPhpObject::createFromPhpObject($user);
    }

    public function onApieResourceCreated(ApieResourceCreated $event): void
    {
        foreach ((new ReflectionClass($event->resource))->getAttributes(Auditable::class) as $auditable) {
            $reference = IdFriendlyEntityReference::createFromContext($event->context);
            if ($reference instanceof IdFriendlyEntityReference) {
                $auditLog = new AuditLog(
                    $reference,
                    SerializedPhpObject::createFromPhpObject($event->resource),
                    new AuditCreate($event->context->getContext(RequestMethod::class, false) === RequestMethod::PUT),
                    $this->createUser($event->context)
                );
                $this->datalayer->persistNew(
                    $auditLog,
                    new BoundedContextId($event->context->getContext(ContextConstants::BOUNDED_CONTEXT_ID))
                );
            }
        }
    }

    public function onApieResourceRead(ApieResourceRead $event): void
    {
        foreach ((new ReflectionClass($event->resource))->getAttributes(Auditable::class) as $auditable) {
            $attribute = $auditable->newInstance();
            if (!$attribute->readEvents) {
                continue;
            }
            $reference = IdFriendlyEntityReference::createFromContext($event->context);

            if ($reference instanceof IdFriendlyEntityReference) {
                $auditLog = new AuditLog(
                    $reference,
                    SerializedPhpObject::createFromPhpObject($event->resource),
                    new AuditRead(),
                    $this->createUser($event->context)
                );
                $this->datalayer->persistNew(
                    $auditLog,
                    new BoundedContextId($event->context->getContext(ContextConstants::BOUNDED_CONTEXT_ID))
                );
            }
        }
    }

    public function onApieResourceReadList(ApieResourceReadList $event): void
    {
        foreach ((new ReflectionClass($event->resource))->getAttributes(Auditable::class) as $auditable) {
            $attribute = $auditable->newInstance();
            if (!$attribute->readAllEvents) {
                continue;
            }
            foreach ($event->resource as $entity) {
                $context = $event->context->withContext(ContextConstants::RESOURCE_ID, Utils::toString($entity->getId()));
                $reference = IdFriendlyEntityReference::createFromContext($event->context);

                if ($reference instanceof IdFriendlyEntityReference) {
                    $auditLog = new AuditLog(
                        $reference,
                        SerializedPhpObject::createFromPhpObject($entity),
                        new AuditRead(fromList: true),
                        $this->createUser($context)
                    );
                    $this->datalayer->persistNew(
                        $auditLog,
                        new BoundedContextId($context->getContext(ContextConstants::BOUNDED_CONTEXT_ID))
                    );
                }
            }
        }
    }

    public function onApieResourceModified(ApieResourceModified $event): void
    {
        foreach ((new ReflectionClass($event->resource))->getAttributes(Auditable::class) as $auditable) {
            $reference = IdFriendlyEntityReference::createFromContext($event->context);
            $content = $event->context->getContext(ContextConstants::RAW_CONTENTS, false);

            if ($reference instanceof IdFriendlyEntityReference) {
                $auditLog = new AuditLog(
                    $reference,
                    SerializedPhpObject::createFromPhpObject($event->resource),
                    new AuditModified($content),
                    $this->createUser($event->context)
                );
                $this->datalayer->persistNew(
                    $auditLog,
                    new BoundedContextId($event->context->getContext(ContextConstants::BOUNDED_CONTEXT_ID))
                );
            }
        }
    }

    public function onApieResourceMethodCalled(ApieResourceMethodCalled $event): void
    {
        foreach ((new ReflectionClass($event->resource))->getAttributes(Auditable::class) as $auditable) {
            $reference = IdFriendlyEntityReference::createFromContext($event->context);
            
            if ($reference instanceof IdFriendlyEntityReference) {
                $auditLog = new AuditLog(
                    $reference,
                    SerializedPhpObject::createFromPhpObject($event->resource),
                    new AuditMethodCalled($event->methodName, $event->context->getContext(ContextConstants::RAW_CONTENTS, false)),
                    $this->createUser($event->context)
                );
                $this->datalayer->persistNew(
                    $auditLog,
                    new BoundedContextId($event->context->getContext(ContextConstants::BOUNDED_CONTEXT_ID))
                );
            }
        }
    }

    public function onApieResourceRemoved(ApieResourceRemoved $event): void
    {
        foreach ((new ReflectionClass($event->resource))->getAttributes(Auditable::class) as $auditable) {
            $reference = IdFriendlyEntityReference::createFromContext($event->context);
            
            if ($reference instanceof IdFriendlyEntityReference) {
                $auditLog = new AuditLog(
                    $reference,
                    SerializedPhpObject::createFromPhpObject($event->resource),
                    new AuditRemoved(),
                    $this->createUser($event->context)
                );
                $this->datalayer->persistNew(
                    $auditLog,
                    new BoundedContextId($event->context->getContext(ContextConstants::BOUNDED_CONTEXT_ID))
                );
            }
        }
    }
}
