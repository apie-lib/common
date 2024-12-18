<?php

namespace Apie\Common\ErrorHandler;

use Apie\Core\Exceptions\HttpStatusCodeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ApiErrorRenderer
{
    /**
     * @TODO move to PSR request + response?
     */
    public function createApiResponse(Throwable $error): Response
    {
        $statusCode = $error instanceof HttpStatusCodeException ? $error->getStatusCode() : 500;
        return new JsonResponse(
            [
                'message' => $error->getMessage(),
                'code' => $error->getCode(),
            ],
            $statusCode
        );
    }
}
