<?php
namespace Apie\Common\Translator;

use Apie\Core\ContextBuilders\ContextBuilderFactory;
use Apie\Core\Translator\Lists\TranslationStringSet;

final class TranslationCollector
{
    /**
     * @var array<int, TranslationStringProviderInterface>
     */
    private array $translationStringProviders;

    public function __construct(
        private readonly ContextBuilderFactory $contextBuilderFactory,
        TranslationStringProviderInterface... $translationStringProviders
    ) {
        $this->translationStringProviders = $translationStringProviders;
    }

    /**
     * For Symfony tagged_iterator can not be variadic
     *
     * @param iterable<string|int, TranslationStringProviderInterface> $translationStringProviders
     */
    public static function create(
        ContextBuilderFactory $contextBuilderFactory,
        iterable $translationStringProviders
    ): static {
        return new static(
            $contextBuilderFactory,
            ...$translationStringProviders
        );
    }

    public function createList(): TranslationStringSet
    {
        $collected = [];
        $context = $this->contextBuilderFactory->createGeneralContext([
            'translation_list' => true,
            TranslationCollector::class => $this
        ]);
        foreach ($this->translationStringProviders as $translationStringProvider) {
            $added = $translationStringProvider->provideStringTranslations($context)->toArray();
            foreach ($added as $addedTranslation) {
                $collected[] = $addedTranslation;
            }
        }
        return new TranslationStringSet($collected);
    }
}
