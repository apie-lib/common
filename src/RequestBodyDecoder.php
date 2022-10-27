<?php
namespace Apie\Common;

use Apie\Core\Exceptions\InvalidTypeException;
use Apie\RestApi\Exceptions\InvalidContentTypeException;
use Apie\Serializer\DecoderHashmap;
use Apie\Serializer\Interfaces\DecoderInterface;
use Psr\Http\Message\ServerRequestInterface;

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
        if (!isset($this->decoderHashmap[$contentType])) {
            throw new InvalidContentTypeException($contentType);
        }
        return $this->decoderHashmap[$contentType];
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
        return $rawContents;
    }
}
