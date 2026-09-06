<?php
namespace Apie\Tests\Common\Php84;

use Apie\Core\Context\ApieContext;
use Apie\Fixtures\TestHelpers\ObjectTestCase;
use Apie\Serializer\Serializer;
use BcMath\Number;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;

#[RequiresPhpExtension('bcmath')]
class BcMathTest extends ObjectTestCase
{
    public static function className(): string
    {
        return Number::class;
    }

    public static function getOpenApiSchemaForCreation(): array
    {
        return [
            'type' => 'string',
            'format' => 'bcmath-number',
            'pattern' => true,
            'example' => true,
            'description' => true,
        ];
    }
    #[Test]
    #[DataProvider('provideDenormalize')]
    public function it_can_denormalize_bcmath_number(mixed $expected, mixed $input): void
    {
        $serializer = Serializer::create();
        $actual = $serializer->denormalizeNewObject($input, Number::class, new ApieContext());
        static::assertEquals($expected, $actual);
    }

    public static function provideDenormalize(): array
    {
        return [
            'arbitrary number as string' => [new Number('42'), '42'],
            'arbitrary number as integer' => [new Number('42'), 42],
        ];
    }
}
