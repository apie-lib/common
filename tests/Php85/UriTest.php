<?php
namespace Apie\Tests\Common;

use Apie\Core\Context\ApieContext;
use Apie\Fixtures\TestHelpers\ObjectTestCase;
use Apie\Serializer\Serializer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\Test;
use Uri\Rfc3986\Uri;

#[RequiresPhp('>=8.5')]
class UriTest extends ObjectTestCase
{
    public static function className(): string
    {
        return Uri::class;
    }

    public static function getOpenApiSchemaForCreation(): array
    {
        return [
            'type' => 'string',
            'format' => 'uri',
            'example' => true,
        ];
    }

    #[Test]
    #[DataProvider('provideFromDenormalize')]
    public function it_can_be_denormalize_uri(mixed $expected, mixed $input): void
    {
        $serializer = Serializer::create();
        $actual = $serializer->denormalizeNewObject($input, Uri::class, new ApieContext());
        static::assertEquals($expected, $actual);
    }

    public static function provideFromDenormalize(): array
    {
        return [
            'valid RFC3986 URI' => [new Uri('htttps://www.example.com'), 'https://www.example.com'],
        ];
    }
}
