<?php
namespace Apie\Common\StreamFactory;

use Apie\Core\Actions\ActionResponse;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class EmptyResponseOn204Factory implements StreamFactoryInterface
{
    public function createStream(
        ServerRequestInterface $request,
        ActionResponse $output
    ): ?ResponseInterface {
        $statusCode = $output->getStatusCode();
        if ($statusCode !== 204) {
            return null;
        }

        $psr17Factory = new Psr17Factory();
        return $psr17Factory->createResponse($statusCode);
    }
}
