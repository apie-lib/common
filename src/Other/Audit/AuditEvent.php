<?php
namespace Apie\Common\Other\Audit;

use Apie\Common\Enums\AuditLogEvent;
use Apie\Core\Context\ApieContext;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Translator\ApieTranslatorInterface;
use Apie\Core\ValueObjects\NonEmptyString;

interface AuditEvent
{
    public function getEvent(): AuditLogEvent;

    /**
     * @param class-string<EntityInterface>|EntityInterface|null $entity
     */
    public function getDescription(
        ApieTranslatorInterface $translator,
        ApieContext $context,
        string|EntityInterface|null $entity,
    ): NonEmptyString;
}
