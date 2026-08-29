<?php
namespace Apie\Tests\Common\Other;

use Apie\Common\Other\AuditOrigin;
use Apie\Fixtures\TestHelpers\ValueObjectTestCase;

class AuditOriginTest extends ValueObjectTestCase
{
    public static function className(): string
    {
        return AuditOrigin::class;
    }

    public static function provideFromNative(): array
    {
        $emptyRequest = [
            'clientIp' => null,
            'clientUserAgent' => null
        ];
        $filledRequest = [
            'clientIp' => '127.0.0.1',
            'clientUserAgent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36",
        ];
        $emptyConsole = [
            'serverName' => null,
            'terminalUserName' => null
        ];
        $consoleLog = [
            'serverName' => 'abc',
            'terminalUserName' => 'root',
        ];
        return [
            'empty' => [[...$emptyRequest, ...$emptyConsole], []],
            'http request' => [[...$filledRequest, ...$emptyConsole], $filledRequest],
            'console' => [[...$consoleLog, ...$emptyRequest], $consoleLog],
        ];
    }

    public static function getOpenApiSchemaForCreation(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'clientIp' => ['type' => 'string', 'nullable' => true],
                'clientUserAgent' => ['type' => 'string', 'nullable' => true],
                'serverName' => ['type' => 'string', 'nullable' => true],
                'terminalUserName' => ['type' => 'string', 'nullable' => true],
            ]
        ];
    }
}
