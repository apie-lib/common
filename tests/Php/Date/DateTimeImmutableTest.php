<?php
namespace Apie\Tests\Common\Php\Date;

use Apie\Fixtures\TestHelpers\ObjectTestCase;

class DateTimeImmutableTest extends ObjectTestCase
{
    public static function className(): string
    {
        return \DateTimeImmutable::class;
    }

    public static function getOpenApiSchemaForCreation(): array
    {
        return [
            'type' => 'string',
            'format' => 'datetime',
            'pattern' => true,
        ];
    }
}
