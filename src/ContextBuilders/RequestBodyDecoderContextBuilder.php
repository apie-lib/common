<?php
namespace Apie\Common\ContextBuilders;

use Apie\Common\ContextConstants;
use Apie\Common\RequestBodyDecoder;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextBuilders\ContextBuilderInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestBodyDecoderContextBuilder implements ContextBuilderInterface
{
    public function __construct(private readonly RequestBodyDecoder $requestBodyDecoder)
    {
    }

    public function process(ApieContext $context): ApieContext
    {
        if (!$context->hasContext(ContextConstants::RAW_CONTENTS) && $context->hasContext(ServerRequestInterface::class)) {
            return $context->withContext(
                ContextConstants::RAW_CONTENTS,
                $this->requestBodyDecoder->decodeBody(
                    $context->getContext(ServerRequestInterface::class)
                )
            );
        }

        return $context;
    }
}
