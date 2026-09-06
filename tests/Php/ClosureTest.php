<?php
namespace Apie\Tests\Common\Php;

use Apie\Fixtures\Attributes\DisableDatalayerTest;
use Apie\Fixtures\TestHelpers\ObjectTestCase;

#[DisableDatalayerTest]
class ClosureTest extends ObjectTestCase
{
    public static function className(): string
    {
        return \Closure::class;
    }

    public static function getOpenApiSchemaForCreation(): array
    {
        return [
            'nullable' => false,
        ];
    }
}
