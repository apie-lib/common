<?php
namespace Apie\Tests\Common\Php84;

use Apie\Fixtures\TestHelpers\ObjectTestCase;
use RoundingMode;

class RoundingModeTest extends ObjectTestCase
{
    public static function className(): string
    {
        return RoundingMode::class;
    }

    public static function getOpenApiSchemaForCreation(): array
    {
        return [
            'type' => 'string',
            'enum' => [
                RoundingMode::HalfAwayFromZero->name,
                RoundingMode::HalfTowardsZero->name,
                RoundingMode::HalfEven->name,
                RoundingMode::HalfOdd->name,
                RoundingMode::TowardsZero->name,
                RoundingMode::AwayFromZero->name,
                RoundingMode::NegativeInfinity->name,
                RoundingMode::PositiveInfinity->name,
            ],
        ];
    }
}
