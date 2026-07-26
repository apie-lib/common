<?php
namespace Apie\Common\Translator;

use Apie\Core\Context\ApieContext;
use Apie\Core\Translator\Lists\TranslationStringSet;

interface TranslationStringProviderInterface
{
    public function provideStringTranslations(ApieContext $apieContext): TranslationStringSet;
}
