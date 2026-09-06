<?php
namespace Apie\Tests\Common\Php\Dom;

use Apie\Core\Context\ApieContext;
use Apie\Fixtures\TestHelpers\ObjectTestCase;
use Apie\Serializer\Serializer;
use DOMDocument;
use DOMElement;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;

#[RequiresPhpExtension('dom')]
class DomElementTest extends ObjectTestCase
{
    public static function className(): string
    {
        return DOMElement::class;
    }

    #[Test]
    public function it_can_be_normalized_with_serializer(): void
    {
        $document = new DOMDocument();
        $element = $document->createElement('root', 'Hello');
        $document->appendChild($element);

        $actual = Serializer::create()->normalize($element, new ApieContext());

        self::assertSame('<root>Hello</root>', $actual);
    }

    #[Test]
    public function it_can_be_denormalized_with_serializer(): void
    {
        $actual = Serializer::create()->denormalizeNewObject('<root>Hello</root>', DOMElement::class, new ApieContext());

        self::assertInstanceOf(DOMElement::class, $actual);
        self::assertSame('root', $actual->tagName);
        self::assertSame('Hello', $actual->textContent);
    }

    public static function getOpenApiSchemaForCreation(): array
    {
        return [
            'type' => 'string',
            'format' => 'xml',
            'description' => true,
        ];
    }
}
