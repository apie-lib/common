<?php
namespace Apie\Tests\Common\Php\Ffi;

use Apie\Core\Context\ApieContext;
use Apie\Fixtures\TestHelpers\ObjectTestCase;
use Apie\Serializer\Serializer;
use FFI;
use FFI\CType;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;

#[RequiresPhpExtension('ffi')]
class FfiCtypeTest extends ObjectTestCase
{
    public static function className(): string
    {
        return CType::class;
    }

    #[Test]
    public function it_can_be_normalized_with_serializer(): void
    {
        $type = FFI::cdef()->type('int[10]');

        $actual = Serializer::create()->normalize($type, new ApieContext());

        self::assertSame('int32_t[10]', $actual);
    }

    #[Test]
    public function it_can_be_denormalized_with_serializer(): void
    {
        $actual = Serializer::create()->denormalizeNewObject('unsigned int', CType::class, new ApieContext());

        self::assertInstanceOf(CType::class, $actual);
        self::assertSame('uint32_t', $actual->getName());
    }

    public static function getOpenApiSchemaForCreation(): array
    {
        return [
            'type' => 'string',
            'pattern' => true,
        ];
    }
}
