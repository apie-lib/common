<?php
namespace Apie\Tests\Common;

use Apie\Core\Context\ApieContext;
use Apie\Fixtures\TestHelpers\TestWithFaker;
use Apie\Fixtures\TestHelpers\TestWithOpenapiSchema;
use Apie\Serializer\Serializer;
use GMP;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[RequiresPhpExtension('gmp')]
class GmpTest extends TestCase
{
    use TestWithFaker;
    use TestWithOpenapiSchema;

    #[Test]
    #[DataProvider('provideFromNative')]
    public function it_can_denormalize_gmp(mixed $expected, mixed $input): void
    {
        $serializer = Serializer::create();
        $actual = $serializer->denormalizeNewObject($input, GMP::class, new ApieContext());
        static::assertEquals($expected, $actual);
    }

    public static function provideFromNative(): array
    {
        return [
            'arbitrary number as string' => [new GMP('42'), '42'],
            'arbitrary number as integer' => [new GMP('42'), 42],
        ];
    }

    #[Test]
    public function it_works_with_schema_generator()
    {
        $this->runOpenapiSchemaTestForCreation(
            GMP::class,
            'GMP-post',
            [
                'type' => 'string',
                'format' => 'bigint',
                'pattern' => true,
                'example' => true,
                'description' => true,
            ]
        );
    }

    #[Test]
    public function it_works_with_apie_faker()
    {
        $this->runFakerTest(GMP::class);
    }
}
