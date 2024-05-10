<?php
namespace Apie\Common\ContextBuilders\Exceptions;

use Apie\Core\Exceptions\ApieException;
use Apie\Core\Exceptions\HttpStatusCodeException;
use Throwable;

final class WrongTokenException extends ApieException implements HttpStatusCodeException
{
    public function __construct(?Throwable $previous)
    {
        parent::__construct('Session cookie could not be decrypted', 0, $previous);
    }

    public function getStatusCode(): int
    {
        return 401;
    }
}
