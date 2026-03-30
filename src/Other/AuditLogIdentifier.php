<?php
namespace Apie\Common\Other;

use Apie\Core\Identifiers\IdentifierInterface;
use Apie\Core\ValueObjects\EntityReference;
use Apie\Core\ValueObjects\IdFriendlyEntityReference;
use Apie\Core\ValueObjects\SnowflakeIdentifier;
use ReflectionClass;

/**
 * @implements IdentifierInterface<AuditLog>
 */
class AuditLogIdentifier extends SnowflakeIdentifier implements IdentifierInterface
{
    public function __construct(
        private IdFriendlyEntityReference $entityReference,
        private float $microtime
    ) {
    }

    public function getEntityReference(): IdFriendlyEntityReference
    {
        return $this->entityReference;
    }

    public function getMicrotime(): float
    {
        return $this->microtime;
    }

    protected static function getSeparator(): string
    {
        return '.-.';
    }

    public static function getReferenceFor(): ReflectionClass
    {
        return new ReflectionClass(AuditLog::class);
    }
}
