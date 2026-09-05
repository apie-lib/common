<?php
namespace Apie\Tests\Common;

use Apie\Core\Context\ApieContext;
use Apie\Fixtures\TestHelpers\TestWithFaker;
use Apie\Fixtures\TestHelpers\TestWithOpenapiSchema;
use Apie\Serializer\Serializer;
use BcMath\Number;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[RequiresPhpExtension('bcmath')]
class BcMathTest extends TestCase
{
    use TestWithFaker;
    use TestWithOpenapiSchema;

    #[Test]
    #[DataProvider('provideFromNative')]
    public function it_can_denormalize_bcmath_number(mixed $expected, mixed $input): void
    {
        $serializer = Serializer::create();
        $actual = $serializer->denormalizeNewObject($input, Number::class, new ApieContext());
        static::assertEquals($expected, $actual);
    }

    public static function provideFromNative(): array
    {
        return [
            'arbitrary number as string' => [new Number('42'), '42'],
            'arbitrary number as integer' => [new Number('42'), 42],
        ];
    }

    #[Test]
    public function it_works_with_schema_generator()
    {
        $this->runOpenapiSchemaTestForCreation(
            Number::class,
            'Number-post',
            [
                'type' => 'string',
                'format' => 'bcmath-number',
                'pattern' => true,
                'example' => true,
                'description' => true,
            ]
        );
    }

    #[Test]
    public function it_works_with_apie_faker()
    {
        $this->runFakerTest(Number::class);
    }
}
