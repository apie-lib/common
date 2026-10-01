<?php
namespace Apie\Tests\Common\Php\Reflection;

use Apie\Core\Context\ApieContext;
use Apie\Fixtures\TestHelpers\ObjectTestCase;
use Apie\Serializer\Serializer;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;

class ReflectionClassTest extends ObjectTestCase
{
    public static function className(): string
    {
        return ReflectionClass::class;
    }

    public static function getOpenApiSchemaForCreation(): array
    {
        return [
            'type' => 'string',
            'example' => true,
        ];
    }

    #[Test]
    public function it_serializes_to_a_string()
    {
        $serializer = Serializer::create();
        $this->assertEquals(
            __CLASS__,
            $serializer->normalize(new ReflectionClass(__CLASS__), new ApieContext())
        );

        $this->assertEquals(
            new ReflectionClass(__CLASS__),
            $serializer->denormalizeNewObject(__CLASS__, ReflectionClass::class, new ApieContext())
        );
    }

}
