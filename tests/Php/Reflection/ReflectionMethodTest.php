<?php
namespace Apie\Tests\Common\Php\Reflection;

use Apie\Core\Context\ApieContext;
use Apie\Fixtures\TestHelpers\ObjectTestCase;
use Apie\Serializer\Serializer;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use ReflectionMethod;

class ReflectionMethodTest extends ObjectTestCase
{
    public static function className(): string
    {
        return ReflectionMethod::class;
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
        $input = (new ReflectionClass(__CLASS__))->getMethod(__FUNCTION__);
        $serializer = Serializer::create();
        $this->assertEquals(
            __METHOD__,
            $serializer->normalize($input, new ApieContext())
        );

        $this->assertEquals(
            $input,
            $serializer->denormalizeNewObject(__METHOD__, ReflectionMethod::class, new ApieContext())
        );
    }

}
