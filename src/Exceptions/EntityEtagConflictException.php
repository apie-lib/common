<?php
namespace Apie\Common\Exceptions;

use Apie\Core\Exceptions\ApieException;
use Apie\Core\Exceptions\HttpStatusCodeException;

final class EntityEtagConflictException extends ApieException implements HttpStatusCodeException
{
    public function __construct()
    {
        parent::__construct('The resource has changed since the supplied ETag.');
    }

    public function getStatusCode(): int
    {
        return 409;
    }
}
