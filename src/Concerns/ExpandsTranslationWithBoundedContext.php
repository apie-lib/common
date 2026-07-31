<?php
namespace Apie\Common\Concerns;

use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\Context\ApieContext;
use Apie\Core\Identifiers\SnakeCaseSlug;
use Apie\Core\Translator\ValueObjects\AbstractTranslation;
use Generator;

trait ExpandsTranslationWithBoundedContext
{
    /**
     * @param iterable<int, AbstractTranslation> $translations
     * @return Generator<int, AbstractTranslation>
     */
    protected function iterateOverTranslationWithAllResources(
        ApieContext $apieContext,
        iterable $translations,
        bool $includeWithoutBoundedContext
    ): Generator {
        if ($includeWithoutBoundedContext) {
            foreach ($translations as $translation) {
                yield $translation
                    ->withoutBoundedContextId()
                    ->withoutResourceIdentifier();
            }
        }
        $hashmap = $apieContext->getContext(BoundedContextHashmap::class, false);
        if ($hashmap instanceof BoundedContextHashmap) {
            foreach ($hashmap->getTupleIterator() as $tuple) {
                foreach ($translations as $translation) {
                    yield $translation
                        ->withBoundedContextId($tuple->boundedContext->getId())
                        ->withResourceIdentifier(SnakeCaseSlug::fromClass($tuple->resourceClass));
                }
            }
        }
    }
}
