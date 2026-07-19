<?php
namespace Apie\Common\ContextBuilders;

use Apie\Core\Context\ApieContext;
use Apie\Core\ContextBuilders\ContextBuilderInterface;
use Apie\Core\ContextConstants;
use Apie\Core\Utils\LanguageParser;
use Apie\Core\ValueObjects\Utils;
use Apie\Serializer\Exceptions\NotAcceptedException;
use Apie\TypeConverter\ReflectionTypeFactory;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Context builder that reads the Accept-Language and Content-Language headers and adds them to the context.
 */
class LocaleContextBuilder implements ContextBuilderInterface
{
    public function __construct(
        private readonly ?string $languageTypehint
    ) {
    }

    public function process(ApieContext $context): ApieContext
    {
        $request = $context->getContext(ServerRequestInterface::class, false);
        if ($request instanceof ServerRequestInterface) {
            $acceptLanguage = $request->getHeaderLine('Accept-Language');
            if ($acceptLanguage) {
                $locales = LanguageParser::parseLanguageHeader($acceptLanguage);
                if (!empty($locales)) {
                    $locale = $this->pickLocale($locales, 'Accept-Language');
                    if (is_object($locale)) {
                        $context = $context->registerInstance($locale);
                    }
                    $context = $context->withContext(ContextConstants::ACCEPT_LOCALE, Utils::toString($locale));
                    $context = $context->withContext(ContextConstants::ACCEPTED_LOCALES, $locales);
                }
            }

            $contentLanguage = $request->getHeaderLine('Content-Language');
            if ($contentLanguage) {
                $locales = LanguageParser::parseLanguageHeader($contentLanguage);
                if (!empty($locales)) {
                    $locale = $this->pickLocale($locales, 'Content-Language');
                    if (is_object($locale)) {
                        $context = $context->registerInstance($locale);
                    }
                    $context = $context->withContext(ContextConstants::LOCALE, Utils::toString($locale));
                    $context = $context->withContext(ContextConstants::DATA_LOCALE, Utils::toString($locale));
                }
            }
        }

        if (!$context->getContext(ContextConstants::DATA_LOCALE, false)) {
            $acceptLanguage = $context->getContext(ContextConstants::ACCEPT_LOCALE, false);
            $contentLanguage = $context->getContext(ContextConstants::DATA_LOCALE, false);
            if ($acceptLanguage) {
                $localeObject = $this->pickLocale(
                    [$acceptLanguage],
                    'framework locale'
                );
                if (is_object($localeObject)) {
                    $context = $context->registerInstance($localeObject);
                }
            }
        }


        return $context;
    }

    /**
     * @param array<int, string> $locales
     */
    private function pickLocale(array $locales, string $headerName): mixed
    {
        if ($this->languageTypehint === null) {
            return $locales[0];
        }
        $last = null;
        foreach ($locales as $locale) {
            try {
                return Utils::toTypehint(
                    // @phpstan-ignore-next-line argument.type
                    ReflectionTypeFactory::createReflectionType($this->languageTypehint),
                    $locale
                );
            } catch (\Throwable $err) {
                $last = $err;
            }
        }
        throw new NotAcceptedException($headerName, $last);
    }
}
