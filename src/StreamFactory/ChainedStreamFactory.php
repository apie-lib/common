<?php
namespace Apie\Common\StreamFactory;

use Apie\Core\Actions\ActionResponse;
use Apie\Serializer\EncoderHashmap;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ChainedStreamFactory implements StreamFactoryInterface
{
    /**
     * @var array<int, StreamFactoryInterface> $factories
     */
    private array $factories;

    public function __construct(
        private readonly EncoderHashmap $encoderHashmap,
        StreamFactoryInterface... $factories
    ) {
        $this->factories = $factories;
    }

    /**
     * @param iterable<int, StreamFactoryInterface> $additionalFactories
     */
    public static function create(
        ?EncoderHashmap $encoderHashmap = null,
        iterable $additionalFactories = []
    ): self {
        $encoderHashmap ??= EncoderHashmap::create();
        return new self(
            $encoderHashmap,
            new EmptyResponseOn204Factory(),
            new PsrReponseFactory($encoderHashmap),
            ...$additionalFactories
        );
    }

    public function createStream(
        ServerRequestInterface $request,
        ActionResponse $output
    ): ResponseInterface {
        foreach ($this->factories as $factory) {
            $result = $factory->createStream($request, $output);
            if ($result) {
                return $result;
            }
        }

        $contentType = $this->encoderHashmap->getAcceptedContentTypeForRequest($request);
        $encoder = $this->encoderHashmap[$contentType];
        
        $psr17Factory = new Psr17Factory();
        $nativeData = $output->getResultAsNativeData();
        $stream = $psr17Factory->createStream($encoder->encode($nativeData));

        return $response = $psr17Factory->createResponse($output->getStatusCode())
            ->withBody($stream)
            ->withHeader('Content-Type', $contentType);
    }
}
