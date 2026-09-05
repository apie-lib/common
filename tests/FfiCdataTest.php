<?php
namespace Apie\Tests\Common;

use Apie\Core\Context\ApieContext;
use Apie\Core\Lists\ItemList;
use Apie\Fixtures\Attributes\DisableDatalayerTest;
use Apie\Fixtures\TestHelpers\ObjectTestCase;
use Apie\Serializer\Serializer;
use FFI;
use FFI\CData;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;

#[RequiresPhpExtension('ffi')]
#[DisableDatalayerTest]
class FfiCdataTest extends ObjectTestCase
{
    public static function className(): string
    {
        return CData::class;
    }

    #[Test]
    public function it_can_be_normalized_with_serializer(): void
    {
        $ffi = FFI::cdef();
        $data = $ffi->new('int[3]');
        $data[0] = 1;
        $data[1] = 2;
        $data[2] = 3;

        $actual = Serializer::create()->normalize($data, new ApieContext());

        self::assertEquals(new ItemList([1, 2, 3]), $actual);
    }

    #[Test]
    #[DataProvider('denormalizeProvider')]
    public function it_can_be_denormalized_with_serializer(mixed $expected, mixed $input): void
    {
        $actual = Serializer::create()->denormalizeNewObject($input, CData::class, new ApieContext());

        self::assertInstanceOf(CData::class, $actual);
        self::assertSame($expected, $actual->cdata);
    }

    public static function denormalizeProvider(): array
    {
        return [
            'integer' => [42, 42],
            'string' => ['H', 'H'],
            'boolean' => [true, true],
        ];
    }

    public static function getOpenApiSchemaForCreation(): array
    {
        return [
            'anyOf' => [
                ['type' => 'string', 'nullable' => true],
                ['type' => 'number', 'nullable' => false],
                ['type' => 'boolean', 'nullable' => false],
                ['type' => 'array', 'items' => ['type' => 'string'], 'nullable' => false],
                ['type' => 'array', 'items' => ['type' => 'number'], 'nullable' => false],
                ['type' => 'array', 'items' => ['type' => 'boolean'], 'nullable' => false],
            ],
            'nullable' => true,
        ];
    }
}
