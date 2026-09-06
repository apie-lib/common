<?php
namespace Apie\Tests\Common\Php;

use Apie\Core\Context\ApieContext;
use Apie\Core\Lists\ItemHashmap;
use Apie\Fixtures\TestHelpers\ObjectTestCase;
use Apie\Serializer\Serializer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use StreamBucket;

class StreamBucketTest extends ObjectTestCase
{
    public static function className(): string
    {
        return StreamBucket::class;
    }

    #[Test]
    public function it_can_be_normalized_with_serializer()
    {
        $stream = fopen('php://memory', 'r+');

        $string = "Hello, world!";

        $bucket = stream_bucket_new($stream, $string);
        $serializer = Serializer::create();
        $actual = $serializer->normalize($bucket, new ApieContext());
        $this->assertEquals(
            new ItemHashmap([
                'data' => 'Hello, world!',
                'dataLength' => strlen($string),
            ]),
            $actual
        );
    }

    #[DataProvider('denormalizeProvider')]
    #[Test]
    public function it_can_be_denormalized_with_serializer(string $expectedString, mixed $input)
    {
        $serializer = Serializer::create();
        $actual = $serializer->denormalizeNewObject($input, StreamBucket::class, new ApieContext());
        $this->assertInstanceOf(StreamBucket::class, $actual);
        $this->assertEquals($expectedString, $actual->data);
        $this->assertEquals(strlen($expectedString), $actual->dataLength);
    }

    public static function denormalizeProvider(): \Generator
    {
        yield 'simple string' => ['Hello', 'Hello'];
        yield 'buffer structure' => ['Hello', ['data' => 'Hello', 'dataLength' => 5]];
        yield 'datalength too small' => ['He', ['data' => 'Hello', 'dataLength' => 2]];
        yield 'datalength too large' => ['Hello', ['data' => 'Hello', 'dataLength' => 6]];
    }

    public static function getOpenApiSchemaForCreation(): array
    {
        return [
            'type' => 'object',
            'required' => ['data', 'dataLength'],
            'properties' => [
                'data' => ['type' => 'string', 'format' => 'binary', 'nullable' => false],
                'dataLength' => ['type' => 'integer', 'minimum' => 0, 'nullable' => false]
            ]
        ];
    }
}
