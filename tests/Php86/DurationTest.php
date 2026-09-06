<?php
namespace Apie\Tests\Common\Php86;

use Apie\Core\Context\ApieContext;
use Apie\Fixtures\TestHelpers\ObjectTestCase;
use Apie\Serializer\Serializer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\Test;
use Time\Duration;

#[RequiresPhp('>=8.6')]
class DurationTest extends ObjectTestCase
{
    public static function className(): string
    {
        return Duration::class;
    }

    public static function getOpenApiSchemaForCreation(): array
    {
        return [
                'oneOf' => [
                    [
                        'type' => 'object',
                        'description' => 'Represents a duration in seconds',
                        'required' => ['seconds', 'nanoseconds', 'negative'],
                        'properties' => [
                            'seconds' => [
                                'type' => 'integer',
                                'description' => 'The number of seconds in the duration',
                                'minimum' => 0,
                                'maximum' => min(PHP_INT_MAX, 9223372035),
                            ],
                            'nanoseconds' => [
                                'type' => 'integer',
                                'description' => 'The number of nanoseconds in the duration',
                                'minimum' => 0,
                                'maximum' => 999999999,
                            ],
                            'negative' => [
                                'type' => 'boolean',
                                'description' => 'Whether the duration is negative',
                            ],
                        ],
                        'example' => [
                            'seconds' => 0,
                            'nanoseconds' => 6000000,
                            'negative' => false,
                        ],
                    ],
                    [
                        'type' => 'number',
                        'format' => 'integer',
                        'description' => 'A duration in milliseconds',
                        'example' => 3600000,
                        'minimum' => 0,
                        'maximum' => min(PHP_INT_MAX, 9223372035999),
                    ],
                ]
            ];
    }

    #[Test]
    #[DataProvider('provideDenormalize')]
    public function it_can_denormalize_Duration(mixed $expected, mixed $input): void
    {
        $serializer = Serializer::create();
        $actual = $serializer->denormalizeNewObject($input, Duration::class, new ApieContext());
        static::assertEquals($expected, $actual);
    }

    public static function provideDenormalize(): array
    {
        return [
            'positive integer' => [Duration::fromMilliseconds(20), 20],
            'negative integer' => [Duration::fromMilliseconds(20)->negate(), -20],
            'floating point' => [Duration::fromMilliseconds(20), 20.5],
            'duration data structure' => [Duration::fromMilliseconds(20), [
                'seconds' => 0,
                'nanoseconds' => 20000000,
                'negative' => false,
            ]],
            'duration data structure negative' => [Duration::fromMilliseconds(20)->negate(), [
                'seconds' => 0,
                'nanoseconds' => 20000000,
                'negative' => true,
            ]],
        ];
    }
}
