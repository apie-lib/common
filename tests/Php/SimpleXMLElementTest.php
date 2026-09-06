<?php
namespace Apie\Tests\Common\Php;

use Apie\Core\Context\ApieContext;
use Apie\Fixtures\TestHelpers\ObjectTestCase;
use Apie\Serializer\Serializer;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;
use SimpleXMLElement;

#[RequiresPhpExtension('simplexml')]
class SimpleXMLElementTest extends ObjectTestCase
{
    public static function className(): string
    {
        return SimpleXMLElement::class;
    }

    #[Test]
    public function it_can_be_normalized_with_serializer(): void
    {
        $xml = new SimpleXMLElement('<root><message>Hello</message></root>');

        $actual = Serializer::create()->normalize($xml, new ApieContext());

        self::assertSame("<?xml version=\"1.0\"?>\n<root><message>Hello</message></root>\n", $actual);
    }

    #[Test]
    public function it_can_be_denormalized_with_serializer(): void
    {
        $actual = Serializer::create()->denormalizeNewObject(
            '<root><message>Hello</message></root>',
            SimpleXMLElement::class,
            new ApieContext()
        );

        self::assertInstanceOf(SimpleXMLElement::class, $actual);
        self::assertSame('Hello', (string) $actual->message);
    }

    public static function getOpenApiSchemaForCreation(): array
    {
        return [
            'type' => 'string',
        ];
    }
}
