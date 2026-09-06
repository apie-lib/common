<?php
namespace Apie\Tests\Common\Php\Date;

use Apie\Fixtures\TestHelpers\ObjectTestCase;

class DateIntervalTest extends ObjectTestCase
{
    public static function className(): string
    {
        return \DateInterval::class;
    }

    public static function getOpenApiSchemaForCreation(): array
    {
        return [
            'type' => 'string',
            'format' => 'duration',
            'description' => true,
        ];
    }
}
