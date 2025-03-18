<?php
namespace Apie\Common\ContextBuilders;

use Apie\Common\RequestBodyDecoder;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextBuilders\ContextBuilderInterface;
use Apie\Core\ContextConstants;
use Apie\Serializer\FieldFilters\FieldFilterInterface;
use Apie\Serializer\FieldFilters\FilterFromArray;
use Apie\Serializer\Interfaces\DecoderInterface;
use Apie\Serializer\Relations\EmbedRelationFromArray;
use Apie\Serializer\Relations\EmbedRelationInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestBodyDecoderContextBuilder implements ContextBuilderInterface
{
    public function __construct(private readonly RequestBodyDecoder $requestBodyDecoder)
    {
    }

    public function process(ApieContext $context): ApieContext
    {
        $request = $context->getContext(ServerRequestInterface::class, false);
        if ($request instanceof ServerRequestInterface) {
            $queryParams = $request->getQueryParams();
            if (isset($queryParams['fields'])) {
                $context = $context->withContext(
                    FieldFilterInterface::class,
                    FilterFromArray::createFromMixed($queryParams['fields'])
                );
            }
            if (isset($queryParams['relations'])) {
                $context = $context->withContext(
                    EmbedRelationInterface::class,
                    EmbedRelationFromArray::createFromMixed($queryParams['relations'])
                );
            }
            if (!$context->hasContext(ContextConstants::RAW_CONTENTS)) {
                return $context
                    ->withContext(
                        DecoderInterface::class,
                        $this->requestBodyDecoder->getDecoder(
                            $context->getContext(ServerRequestInterface::class)
                        )
                    )
                    ->withContext(
                        ContextConstants::RAW_CONTENTS,
                        $this->requestBodyDecoder->decodeBody(
                            $context->getContext(ServerRequestInterface::class)
                        )
                    );
            }
        }

        return $context;
    }
}
