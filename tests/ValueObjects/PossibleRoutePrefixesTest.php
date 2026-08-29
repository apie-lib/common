<?php

namespace Apie\Tests\Common\ValueObjects;

use Apie\Common\ValueObjects\PossibleRoutePrefixes;
use Apie\Fixtures\TestHelpers\ValueObjectTestCase;

class PossibleRoutePrefixesTest extends ValueObjectTestCase
{
    public static function className(): string
    {
        return PossibleRoutePrefixes::class;
    }

    public static function provideFromNative(): array
    {
        return [
            'string input' => [['cms'], 'cms'],
            'array input' => [['cms', 'api'], ['cms', 'api']]
        ];
    }

    public static function getOpenApiSchemaForCreation(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => [
                '$ref' => '#/components/schemas/mixed'
            ],
        ];
    }
}
