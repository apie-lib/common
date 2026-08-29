<?php
namespace Apie\Tests\Common\Other;

use Apie\Common\Other\AuditLogIdentifier;
use Apie\Fixtures\TestHelpers\ValueObjectTestCase;

class AuditLogIdentifierTest extends ValueObjectTestCase
{
    public static function className(): string
    {
        return AuditLogIdentifier::class;
    }

    public static function provideFromNative(): array
    {
        return [
            'simple' => ['123.000000.-.domain_resource_123', '123.-.domain_resource_123']
        ];
    }

    public static function getOpenApiSchemaForCreation(): array
    {
        return [
            'type' => 'string',
            'format' => 'auditlogidentifier',
            'pattern' => true,
        ];
    }
}
