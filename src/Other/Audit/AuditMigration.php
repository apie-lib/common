<?php
namespace Apie\Common\Other\Audit;

use Apie\Common\Enums\AuditLogEvent;
use Apie\Core\Context\ApieContext;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Translator\ApieTranslatorInterface;
use Apie\Core\Translator\ValueObjects\AuditLogEventMessage;
use Apie\Core\ValueObjects\NonEmptyString;

class AuditMigration implements AuditEvent
{

    public function getEvent(): AuditLogEvent
    {
        return AuditLogEvent::Migration;
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
                AuditLogEventMessage::createMigrationEvent($context)
            )
        );
    }
}
