<?php
namespace Apie\Common\Other\Audit;

use Apie\Common\Enums\AuditLogEvent;
use Apie\Core\Context\ApieContext;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Translator\ApieTranslatorInterface;
use Apie\Core\Translator\ValueObjects\AuditLogEventMessage;
use Apie\Core\ValueObjects\NonEmptyString;

class AuditRead implements AuditEvent
{
    public function __construct(
        private bool $fromList = false
    ) {
    }

    public function getEvent(): AuditLogEvent
    {
        return AuditLogEvent::Read;
    }

    public function getDescription(
        ApieTranslatorInterface $translator,
        ApieContext $context,
        string|EntityInterface|null $entity,
    ): NonEmptyString {
        assert($entity !== null);
        return NonEmptyString::fromNative(
            $translator->getGeneralTranslation(
                $context,
                $this->fromList
                    ? AuditLogEventMessage::createResourceRetrievedEvent($context)
                    : AuditLogEventMessage::createResourceRetrievedInListEvent($context)
            )
        );
    }
}
