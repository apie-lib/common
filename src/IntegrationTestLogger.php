<?php
namespace Apie\Common;

use PHPUnit\Framework\TestCase;
use Throwable;

final class IntegrationTestLogger
{
    private static ?Throwable $loggedException = null;
    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    public static function failTestShowError(int $statusCode = 500): never
    {
        TestCase::fail(
            sprintf(
                'Failed request, got status %s, logged exception: %s' . PHP_EOL . '%s',
                $statusCode,
                self::$loggedException?->getMessage(),
                self::$loggedException?->getTraceAsString()
            )
        );
        
    }

    public static function resetLoggedException(): void
    {
        self::$loggedException = null;
    }

    public static function getLoggedException(): ?Throwable
    {
        return self::$loggedException;
    }

    /**
     * Logs exceptions for integration tests purposes.
     */
    public static function logException(Throwable $error): void
    {
        self::$loggedException = $error;
        if (getenv('PHPUNIT_LOG_INTEGRATION_OUTPUT')) {
            while ($error) {
                $stdErr = @fopen('php://stderr', 'w');
                fwrite($stdErr, get_class($error) . ': ' . $error->getMessage() . PHP_EOL);
                fwrite($stdErr, $error->getTraceAsString() . PHP_EOL);
                fclose($stdErr);
                $error = $error->getPrevious();
            }
        }
    }
}
