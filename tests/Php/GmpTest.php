<?php
namespace Apie\Tests\Common\Php;

use Apie\Core\Context\ApieContext;
use Apie\Fixtures\TestHelpers\ObjectTestCase;
use Apie\Serializer\Serializer;
use GMP;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;

#[RequiresPhpExtension('gmp')]
class GmpTest extends ObjectTestCase
{
    public static function className(): string
    {
        return GMP::class;
    }

    public static function getOpenApiSchemaForCreation(): array
    {
        return [
            'type' => 'string',
            'format' => 'bigint',
            'pattern' => true,
            'example' => true,
            'description' => true,
        ];
    }

    #[Test]
    #[DataProvider('provideDenormalize')]
    public function it_can_denormalize_gmp(mixed $expected, mixed $input): void
    {
        $serializer = Serializer::create();
        $actual = $serializer->denormalizeNewObject($input, GMP::class, new ApieContext());
        static::assertEquals($expected, $actual);
    }

    public static function provideDenormalize(): array
    {
        return [
            'arbitrary number as string' => [new GMP('42'), '42'],
            'arbitrary number as integer' => [new GMP('42'), 42],
        ];
    }
}
