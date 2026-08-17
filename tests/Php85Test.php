<?php
namespace Apie\Tests\Common;

use Apie\Core\Context\ApieContext;
use Apie\Fixtures\TestHelpers\TestWithFaker;
use Apie\Fixtures\TestHelpers\TestWithOpenapiSchema;
use Apie\Serializer\Serializer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Uri\Rfc3986\Uri;

#[RequiresPhp('>=8.5')]
class Php85Test extends TestCase
{
    use TestWithFaker;
    use TestWithOpenapiSchema;

    #[Test]
    #[DataProvider('provideFromNative')]
    public function it_can_be_denormalize_uri(mixed $expected, mixed $input): void
    {
        $serializer = Serializer::create();
        $actual = $serializer->denormalizeNewObject($input, Uri::class, new ApieContext());
        static::assertEquals($expected, $actual);
    }

    public static function provideFromNative(): array
    {
        return [
            'valid RFC3986 URI' => [new Uri('htttps://www.example.com'), 'https://www.example.com'],
        ];
    }

    #[Test]
    public function it_works_with_schema_generator()
    {
        $this->runOpenapiSchemaTestForCreation(
            Uri::class,
            'Uri-post',
            [
                'type' => 'string',
                'format' => 'uri',
                'example' => true,
            ]
        );
    }

    #[Test]
    public function it_works_with_apie_faker()
    {
        $this->runFakerTest(Uri::class);
    }
}
