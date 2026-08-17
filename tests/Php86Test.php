<?php
namespace Apie\Tests\Common;

use Apie\Core\Context\ApieContext;
use Apie\Fixtures\TestHelpers\TestWithFaker;
use Apie\Fixtures\TestHelpers\TestWithOpenapiSchema;
use Apie\Serializer\Serializer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Time\Duration;

#[RequiresPhp('>=8.6')]
class Php86Test extends TestCase
{
    use TestWithFaker;
    use TestWithOpenapiSchema;

    #[Test]
    #[DataProvider('provideFromNative')]
    public function it_can_denormalize_Duration(mixed $expected, mixed $input): void
    {
        $serializer = Serializer::create();
        $actual = $serializer->denormalizeNewObject($input, Duration::class, new ApieContext());
        static::assertEquals($expected, $actual);
    }

    public static function provideFromNative(): array
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

    #[Test]
    public function it_works_with_schema_generator()
    {
        $this->runOpenapiSchemaTestForCreation(
            Duration::class,
            'Duration-post',
            [
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
            ]
        );
    }

    #[Test]
    public function it_works_with_apie_faker()
    {
        $this->runFakerTest(Duration::class);
    }
}
