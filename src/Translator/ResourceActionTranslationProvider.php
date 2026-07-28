<?php
namespace Apie\Common\Translator;

use Apie\Common\ActionDefinitionProvider;
use Apie\Common\ActionDefinitions\CreateResourceActionDefinition;
use Apie\Common\ActionDefinitions\ModifyResourceActionDefinition;
use Apie\Common\ActionDefinitions\RunResourceMethodDefinition;
use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\Translator\Lists\TranslationStringSet;
use Apie\Core\Translator\ValueObjects\ResourceAddResourceButtonLabel;
use Apie\Core\Translator\ValueObjects\ResourceAddResourceHeader;
use Apie\Core\Translator\ValueObjects\ResourceCustomActionResourceButtonLabel;
use Apie\Core\Translator\ValueObjects\ResourceCustomActionResourceHeader;
use Apie\Core\Translator\ValueObjects\ResourceCustomGlobalActionResourceButtonLabel;
use Apie\Core\Translator\ValueObjects\ResourceCustomGlobalActionResourceHeader;
use Apie\Core\Translator\ValueObjects\ResourceModifyResourceButtonLabel;
use Apie\Core\Translator\ValueObjects\ResourceModifyResourceHeader;

final class ResourceActionTranslationProvider implements TranslationStringProviderInterface
{
    public function __construct(
        private readonly ActionDefinitionProvider $actionDefinitionProvider
    ) {
    }
    public function provideStringTranslations(ApieContext $apieContext): TranslationStringSet
    {
        $result = [];
        $hashmap = $apieContext->getContext(BoundedContextHashmap::class, false);
        if ($hashmap instanceof BoundedContextHashmap) {
            foreach ($hashmap as $boundedContext) {
                $actionDefinitions = $this->actionDefinitionProvider->provideActionDefinitions(
                    $boundedContext,
                    $apieContext
                        ->withContext(BoundedContextId::class, $boundedContext->getId())
                        ->withContext(BoundedContext::class, $boundedContext)
                );
                /**
                 * CreateResourceActionDefinition::class,
            ReplaceResourceActionDefinition::class,
            GetResourceActionDefinition::class,
            GetResourceListActionDefinition::class,
            ModifyResourceActionDefinition::class,
            RemoveResourceActionDefinition::class,
            RunGlobalMethodDefinition::class,
            RunResourceMethodDefinition::class,
            DownloadFilesActionDefinition::class,
                */
                foreach ($actionDefinitions as $actionDefinition) {
                    // ReplaceResourceActionDefinition?
                    if ($actionDefinition instanceof CreateResourceActionDefinition) {
                        $result[] = ResourceAddResourceButtonLabel::createFromDefinition($actionDefinition, false);
                        $result[] = ResourceAddResourceButtonLabel::createFromDefinition($actionDefinition, true);
                        $result[] = ResourceAddResourceHeader::createFromDefinition($actionDefinition, false);
                        $result[] = ResourceAddResourceHeader::createFromDefinition($actionDefinition, true);
                    } elseif ($actionDefinition instanceof ModifyResourceActionDefinition) {
                        $result[] = ResourceModifyResourceButtonLabel::createFromDefinition($actionDefinition, false);
                        $result[] = ResourceModifyResourceButtonLabel::createFromDefinition($actionDefinition, true);
                        $result[] = ResourceModifyResourceHeader::createFromDefinition($actionDefinition, false);
                        $result[] = ResourceModifyResourceHeader::createFromDefinition($actionDefinition, true);
                    } elseif ($actionDefinition instanceof RunResourceMethodDefinition) {
                        $className = $actionDefinition->getMethod()->isStatic() ? ResourceCustomGlobalActionResourceButtonLabel::class : ResourceCustomActionResourceButtonLabel::class;
                        $result[] = $className::createFromDefinition($actionDefinition, false);
                        $result[] = $className::createFromDefinition($actionDefinition, true);
                        $className = $actionDefinition->getMethod()->isStatic() ? ResourceCustomGlobalActionResourceHeader::class : ResourceCustomActionResourceHeader::class;
                        $result[] = $className::createFromDefinition($actionDefinition, false);
                        $result[] = $className::createFromDefinition($actionDefinition, true);
                    }
                }
            }
        }
        return new TranslationStringSet($result);
    }
}
