<?php
namespace Apie\Common\Translator;

use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\Context\ApieContext;
use Apie\Core\Translator\Enums\Pluralization;
use Apie\Core\Translator\Lists\TranslationStringSet;
use Apie\Core\Translator\ValueObjects\ResourceName;
use Apie\Core\Translator\ValueObjects\TranslationStringSuffix;

class ResourceNameTranslationProvider implements TranslationStringProviderInterface
{
    public function __construct(
        private readonly BoundedContextHashmap $boundedContextHashmap
    ) {
    }

    public function provideStringTranslations(ApieContext $apieContext): TranslationStringSet
    {
        $items = [];
        foreach ($this->boundedContextHashmap->getTupleIterator() as $tuple) {
            $items[] = ResourceName::createFromTuple($tuple, new TranslationStringSuffix(Pluralization::Singular, false));
            $items[] = ResourceName::createFromTuple($tuple, new TranslationStringSuffix(Pluralization::Singular, true));
            
            $items[] = ResourceName::createFromTuple($tuple, new TranslationStringSuffix(Pluralization::Plural, false));
            $items[] = ResourceName::createFromTuple($tuple, new TranslationStringSuffix(Pluralization::Plural, true));
        }
        return new TranslationStringSet($items);
    }
}
