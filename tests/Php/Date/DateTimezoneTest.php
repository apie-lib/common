<?php
namespace Apie\Tests\Common\Php\Date;

use Apie\Fixtures\TestHelpers\ObjectTestCase;

class DateTimezoneTest extends ObjectTestCase
{
    public static function className(): string
    {
        return \DateTimezone::class;
    }

    public static function getOpenApiSchemaForCreation(): array
    {
        return [
            'type' => 'string',
            'format' => 'datetimezone',
            'pattern' => true,
        ];
    }
}
