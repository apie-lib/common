<?php
namespace Apie\Common;

use Apie\Common\ContextBuilders\BoundedContextProviderContextBuilder;
use Apie\Common\ContextBuilders\RequestBodyDecoderContextBuilder;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\ContextBuilders\ContextBuilderFactory as ContextBuildersContextBuilderFactory;
use Apie\Core\ContextBuilders\ContextBuilderInterface;
use Apie\Serializer\DecoderHashmap;

final class ContextBuilderFactory
{
    private function __construct()
    {
    }
    public static function create(
        BoundedContextHashmap $boundedContextHashmap,
        DecoderHashmap $decoderHashmap,
        ContextBuilderInterface... $builders
    ): ContextBuildersContextBuilderFactory {
        return new ContextBuildersContextBuilderFactory(
            new BoundedContextProviderContextBuilder($boundedContextHashmap),
            new RequestBodyDecoderContextBuilder(new RequestBodyDecoder($decoderHashmap)),
            ...$builders
        );
    }
}
