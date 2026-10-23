<?php
namespace Apie\Common\Exceptions;

use Apie\Core\Exceptions\ApieException;
use Apie\Core\Exceptions\HttpStatusCodeException;

final class EntityNotModifiedException extends ApieException implements HttpStatusCodeException
{
    public function __construct(public readonly string $etag)
    {
        parent::__construct('The entity has not been modified.');
    }

    public function getStatusCode(): int
    {
        return 304;
    }
}
