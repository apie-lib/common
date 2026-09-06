<?php

namespace Apie\Tests\Common\Php\Date;

use Apie\Fixtures\TestHelpers\ObjectTestCase;

class DatePeriodTest extends ObjectTestCase
{
    public static function className(): string
    {
        return \DatePeriod::class;
    }

    public static function getOpenApiSchemaForCreation(): array
    {
        return [
            'type' => 'object',
            'required' => [
                'startDate',
                'dateInterval',
            ],
            'properties' => [
                'startDate' => [
                    '$ref' => '#/components/schemas/DateTime-nullable-post',
                ],
                'endDate' => [
                    '$ref' => '#/components/schemas/DateTime-nullable-post'
                ],
                'dateInterval' => [
                    '$ref' => '#/components/schemas/DateInterval-nullable-post'
                ],
                'recurrences' => [
                    'type' => 'integer',
                    'nullable' => false,
                ],
                'options' => [
                    'type' => 'integer',
                    'nullable' => false,
                ],
            ],
        ];
    }
}
