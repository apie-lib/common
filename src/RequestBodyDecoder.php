<?php
namespace Apie\Common;

use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\Session\CsrfTokenProvider;
use Apie\RestApi\Exceptions\InvalidContentTypeException;
use Apie\Serializer\DecoderHashmap;
use Apie\Serializer\Encoders\FormSubmitDecoder;
use Psr\Http\Message\ServerRequestInterface;

final class RequestBodyDecoder
{
    public function __construct(
        private readonly DecoderHashmap $decoderHashmap,
        private readonly CsrfTokenProvider $csrfTokenProvider
    ) {
    }

    /**
     * @return array<string|int, mixed>
     */
    public function decodeBody(ServerRequestInterface $request): array
    {
        if ($request->getMethod() === 'GET') {
            return $request->getQueryParams();
        }
        $contentTypes = $request->getHeader('Content-Type');
        if (count($contentTypes) !== 1) {
            throw new InvalidContentTypeException($request->getHeaderLine('Content-Type'));
        }
        $contentType = reset($contentTypes);
        if (!isset($this->decoderHashmap[$contentType])) {
            throw new InvalidContentTypeException($contentType);
        }
        $decoder = $this->decoderHashmap[$contentType];
        $rawContents = $decoder->decode((string) $request->getBody());
        if ($decoder instanceof FormSubmitDecoder) {
            $this->csrfTokenProvider->validateToken($rawContents['_csrf'] ?? 'no token');
        }
        if (!is_array($rawContents)) {
            throw new InvalidTypeException($rawContents, 'array');
        }
        return $rawContents;
    }
}
