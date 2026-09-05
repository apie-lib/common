<?php
namespace Apie\Tests\Common;

use Apie\Core\Context\ApieContext;
use Apie\Fixtures\TestHelpers\ObjectTestCase;
use Apie\Serializer\Serializer;
use DOMAttr;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;

#[RequiresPhpExtension('dom')]
class DOMAttrTest extends ObjectTestCase
{
    public static function className(): string
    {
        return DOMAttr::class;
    }

    #[Test]
    public function it_can_be_normalized_with_serializer(): void
    {
        $actual = Serializer::create()->normalize(new DOMAttr('title', 'Hello & goodbye'), new ApieContext());

        self::assertSame('title="Hello &amp; goodbye"', $actual);
    }

    #[Test]
    public function it_can_be_denormalized_with_serializer(): void
    {
        $actual = Serializer::create()->denormalizeNewObject('title="Hello &amp; goodbye"', DOMAttr::class, new ApieContext());

        self::assertInstanceOf(DOMAttr::class, $actual);
        self::assertSame('title', $actual->name);
        self::assertSame('Hello & goodbye', $actual->value);
    }

    public static function getOpenApiSchemaForCreation(): array
    {
        return ['type' => 'string'];
    }
}
