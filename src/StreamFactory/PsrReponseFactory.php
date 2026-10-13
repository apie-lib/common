<?php
namespace Apie\Common\StreamFactory;

use Apie\Core\Actions\ActionResponse;
use Apie\Core\FileStorage\StoredFile;
use Apie\Serializer\EncoderHashmap;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class PsrReponseFactory implements StreamFactoryInterface
{
    public function __construct(
        private readonly EncoderHashmap $encoderHashmap
    ) {
    }

    public function createStream(
        ServerRequestInterface $request,
        ActionResponse $output
    ): ?ResponseInterface {
        if ($output->result instanceof ResponseInterface) {
            return $output->result;
        }
        if ($output->result instanceof StreamInterface) {
            $contentType = $this->encoderHashmap->getAcceptedContentTypeForRequest($request, 'application/octet-stream');
            $statusCode = $output->getStatusCode();
            if ($statusCode >= 300) {
                $contentType = 'application/octet-stream';
            }
            $psr17Factory = new Psr17Factory();
            return $psr17Factory->createResponse($statusCode)
                ->withAddedHeader('Content-Type', $contentType)
                ->withBody($output->result);
        }
        if ($output->result instanceof UploadedFileInterface) {
            $stream = $output->result->getStream();
            $contentType = $output->result->getClientMediaType();
            if ($output->result instanceof StoredFile) {
                $contentType = $output->result->getServerMimeType();
            }
            $contentType ??= 'application/octet-stream';

            if ($stream->isSeekable()) {
                $stream->rewind();
            }

            $psr17Factory = new Psr17Factory();
            return $psr17Factory->createResponse($output->getStatusCode())
                ->withAddedHeader('Content-Type', $contentType)
                ->withBody($stream);
        }

        return null;
    }
}
