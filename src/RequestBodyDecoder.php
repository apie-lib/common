<?php
namespace Apie\Common;

use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\FileStorage\StoredFile;
use Apie\RestApi\Exceptions\InvalidContentTypeException;
use Apie\Serializer\DecoderHashmap;
use Apie\Serializer\Interfaces\DecoderInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

final class RequestBodyDecoder
{
    public function __construct(
        private readonly DecoderHashmap $decoderHashmap
    ) {
    }

    public function getDecoder(ServerRequestInterface $request): ?DecoderInterface
    {
        if ($request->getMethod() === 'GET') {
            return null;
        }
        $contentTypes = $request->getHeader('Content-Type');
        if (count($contentTypes) !== 1) {
            throw new InvalidContentTypeException($request->getHeaderLine('Content-Type'));
        }
        $contentType = reset($contentTypes);
        $options = null;
        $parsedBody = null;
        if (strpos($contentType, ';') !== false) {
            $options = strstr($contentType, ';', false);
            $contentType = strstr($contentType, ';', true);
        }
        if (strtolower($contentType) === 'multipart/form-data') {
            $parsedBody = $request->getParsedBody();
        }
        if (!isset($this->decoderHashmap[$contentType])) {
            throw new InvalidContentTypeException($contentType);
        }
        $res = $this->decoderHashmap[$contentType]->withParsedBody($parsedBody);
        return $options === null ? $res  : $res->withOptions($options);
    }

    /**
     * @return array<string|int, mixed>
     */
    public function decodeBody(ServerRequestInterface $request): array
    {
        $decoder = $this->getDecoder($request);
        
        if (null === $decoder) {
            return $request->getQueryParams();
        }
        $rawContents = $decoder->decode((string) $request->getBody());
        if (!is_array($rawContents)) {
            throw new InvalidTypeException($rawContents, 'array');
        }
        $this->addUploadedFiles($rawContents, $request->getUploadedFiles()['form'] ?? []);
        return $rawContents;
    }

    /**
     * @param array<string|int, mixed> $rawContents
     * @param array<string|int, mixed> $uploadedFiles
     */
    private function addUploadedFiles(array& $rawContents, array $uploadedFiles): void
    {
        foreach ($uploadedFiles as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $rawContents[$key] = $value instanceof StoredFile ? $value : StoredFile::createFromUploadedFile($value);
                continue;
            }
            if (!isset($rawContents[$key])) {
                $rawContents[$key] = [];
            }
            $this->addUploadedFiles($rawContents[$key], $value);
        }
    }
}
