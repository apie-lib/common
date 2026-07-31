<?php
namespace Apie\Common\Translator;

use Apie\Core\Attributes\ProvideTranslationMethod;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\Context\ApieContext;
use Apie\Core\Translator\Lists\TranslationStringSet;
use Apie\Core\Translator\ValueObjects\AbstractTranslation;

/**
 * Reads ProvideTranslationMethod attribute to add resources
 *
 * @see ProvideTranslationMethod
 */
class ReadAttributeTranslationProvider implements TranslationStringProviderInterface
{
    public function provideStringTranslations(ApieContext $apieContext): TranslationStringSet
    {
        $items = [];
        $boundedContextHashmap = $apieContext->getContext(BoundedContextHashmap::class, false);
        if ($boundedContextHashmap instanceof BoundedContextHashmap) {
            foreach ($boundedContextHashmap->getTupleIterator() as $tuple) {
                foreach ($tuple->resourceClass->getAttributes(ProvideTranslationMethod::class) as $reflAttribute) {
                    $attribute = $reflAttribute->newInstance();
                    $method = $tuple->resourceClass->getMethod($attribute->methodName);
                    if (!$method->isStatic()) {
                        throw new \LogicException('Method ' . $method->name . ' is not static');
                    }
                    $result = $method->invoke(null, $apieContext, $tuple);
                    if (is_iterable($result)) {
                        foreach ($result as $item) {
                            assert($item instanceof  AbstractTranslation);
                            $items[] = $item;
                        }
                    } else {
                        assert($result instanceof AbstractTranslation);
                        $items[] = $result;
                    }
                }
            }
        }
        return new TranslationStringSet($items);
    }
}
