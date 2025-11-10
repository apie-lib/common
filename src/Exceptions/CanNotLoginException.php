<?php
namespace Apie\Common\Exceptions;

use Apie\Core\Exceptions\HttpStatusCodeException;
use Apie\TypeConverter\Exceptions\GetMultipleChainedExceptionInterface;
use LogicException;
use Throwable;

final class CanNotLoginException extends LogicException implements GetMultipleChainedExceptionInterface, HttpStatusCodeException
{
    /**
     * @param array<string, Throwable> $errors
     */
    public function __construct(private array $errors)
    {
        $messages = [];
        $previous = null;
        foreach ($errors as $error) {
            $messages[] = '"' . $error->getMessage() . '"';
            $previous = $error;
        }
        parent::__construct(
            'Can not login: ' . implode(', ', $messages),
            0,
            $previous
        );
    }

    public function getStatusCode(): int
    {
        return 401;
    }

    public function getChainedExceptions(): array
    {
        return $this->errors;
    }
}
