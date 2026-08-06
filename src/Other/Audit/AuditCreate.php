<?php
namespace Apie\Common\Other\Audit;

use Apie\Common\Enums\AuditLogEvent;
use Apie\Core\Context\ApieContext;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Translator\ApieTranslatorInterface;
use Apie\Core\Translator\ValueObjects\AuditLogEventMessage;
use Apie\Core\ValueObjects\NonEmptyString;

class AuditCreate implements AuditEvent
{
    public function __construct(
        private bool $withId
    ) {
    }

    public function getEvent(): AuditLogEvent
    {
        return $this->withId ? AuditLogEvent::Replaced : AuditLogEvent::Created;
    }

    public function getDescription(
        ApieTranslatorInterface $translator,
        ApieContext $context,
        string|EntityInterface|null $entity,
    ): NonEmptyString {
        assert(is_string($entity));
        return NonEmptyString::fromNative(
            $translator->getGeneralTranslation(
                $context,
                $this->withId ? AuditLogEventMessage::createResourceReplacedEvent($context) : AuditLogEventMessage::createResourceCreatedEvent($context)
            )
        );
    }
}
