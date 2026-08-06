<?php
namespace Apie\Common\Other\Audit;

use Apie\Common\Enums\AuditLogEvent;
use Apie\Core\Context\ApieContext;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Translator\ApieTranslatorInterface;
use Apie\Core\Translator\ValueObjects\AuditLogEventMessage;
use Apie\Core\ValueObjects\NonEmptyString;

class AuditRemoved implements AuditEvent
{
    public function getEvent(): AuditLogEvent
    {
        return AuditLogEvent::Removed;
    }

    public function getDescription(
        ApieTranslatorInterface $translator,
        ApieContext $context,
        string|EntityInterface|null $entity,
    ): NonEmptyString {
        return NonEmptyString::fromNative(
            $translator->getGeneralTranslation(
                $context,
                AuditLogEventMessage::createResourceRemovedEvent($context)
            )
        );
    }
}
