<?php
namespace Apie\Common\ContextBuilders;

use Apie\Core\Context\ApieContext;
use Apie\Core\ContextBuilders\ContextBuilderInterface;
use Apie\Core\ContextConstants;
use Apie\Core\Utils\LanguageParser;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Context builder that reads the Accept-Language and Content-Language headers and adds them to the context.
 */
class LocaleContextBuilder implements ContextBuilderInterface
{
    public function process(ApieContext $context): ApieContext
    {
        $request = $context->getContext(ServerRequestInterface::class, false);
        if ($request instanceof ServerRequestInterface) {
            $acceptLanguage = $request->getHeaderLine('Accept-Language');
            if ($acceptLanguage) {
                $locales = LanguageParser::parseLanguageHeader($acceptLanguage);
                if (!empty($locales)) {
                    $context = $context->withContext(ContextConstants::ACCEPT_LOCALE, $locales[0]);
                    $context = $context->withContext(ContextConstants::ACCEPTED_LOCALES, $locales);
                }
            }

            $contentLanguage = $request->getHeaderLine('Content-Language');
            if ($contentLanguage) {
                $locales = LanguageParser::parseLanguageHeader($contentLanguage);
                if (!empty($locales)) {
                    $context = $context->withContext(ContextConstants::LOCALE, $locales[0]);
                    $context = $context->withContext(ContextConstants::DATA_LOCALE, $locales[0]);
                }
            }
        }
        return $context;
    }
}
