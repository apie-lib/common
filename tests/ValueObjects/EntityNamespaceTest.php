<?php

namespace Apie\Tests\Common\ValueObjects;

use Apie\Common\ValueObjects\EntityNamespace;
use Apie\Fixtures\TestHelpers\ValueObjectTestCase;

class EntityNamespaceTest extends ValueObjectTestCase
{
    public static function className(): string
    {
        return EntityNamespace::class;
    }

    public static function provideFromNative(): array
    {
        return [
            'regular namespace' => [__NAMESPACE__ . '\\', __NAMESPACE__],
            'example value' => ["Symfony\Component\\", "Symfony\Component\\"],
        ];
    }

    public static function getOpenApiSchemaForCreation(): array
    {
        return [
            'type' => 'string',
            'format' => 'entitynamespace',
            'description' => true,
            'pattern' => true,
            'example' => true,
        ];
    }
}