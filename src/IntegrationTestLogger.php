<?php
namespace Apie\Common;

use Throwable;

final class IntegrationTestLogger
{
    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Logs exceptions for integration tests purposes.
     */
    public static function logException(Throwable $error): void
    {
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
