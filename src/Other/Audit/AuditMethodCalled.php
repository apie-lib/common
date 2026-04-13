<?php
namespace Apie\Common\Other\Audit;

use Apie\Common\Enums\AuditLogEvent;
use Apie\Core\Context\ApieContext;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Translator\ApieTranslatorInterface;
use Apie\Core\Translator\ValueObjects\TranslationString;
use Apie\Core\ValueObjects\NonEmptyString;

class AuditMethodCalled implements AuditEvent
{
    public function __construct(
        private string $methodName,
        private mixed $contents
    ) {
    }
    public function getEvent(): AuditLogEvent
    {
        return AuditLogEvent::MethodCalled;
    }

    public function getContents(): mixed
    {
        return $this->contents;
    }

    public function getDescription(
        ApieTranslatorInterface $translator,
        ApieContext $context,
        string|EntityInterface|null $entity,
    ): NonEmptyString {
        return NonEmptyString::fromNative(
            $translator->getGeneralTranslation(
                $context,
                new TranslationString('audit_log.method_called.' . $this->methodName)
            )
        );
    }
}
