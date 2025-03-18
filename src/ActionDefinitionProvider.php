<?php
namespace Apie\Common;

use Apie\Common\ActionDefinitions\ActionDefinitionInterface;
use Apie\Common\ActionDefinitions\CreateResourceActionDefinition;
use Apie\Common\ActionDefinitions\DownloadFilesActionDefinition;
use Apie\Common\ActionDefinitions\GetResourceActionDefinition;
use Apie\Common\ActionDefinitions\GetResourceListActionDefinition;
use Apie\Common\ActionDefinitions\ModifyResourceActionDefinition;
use Apie\Common\ActionDefinitions\RemoveResourceActionDefinition;
use Apie\Common\ActionDefinitions\ReplaceResourceActionDefinition;
use Apie\Common\ActionDefinitions\RunGlobalMethodDefinition;
use Apie\Common\ActionDefinitions\RunResourceMethodDefinition;
use Apie\Common\Lists\ActionDefinitionList;
use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\Context\ApieContext;

class ActionDefinitionProvider
{
    /**
     * @var array<int, class-string<ActionDefinitionInterface>>
     */
    private const ACTION_DEFINITION_CLASSES = [
        CreateResourceActionDefinition::class,
        ReplaceResourceActionDefinition::class,
        GetResourceActionDefinition::class,
        GetResourceListActionDefinition::class,
        ModifyResourceActionDefinition::class,
        RemoveResourceActionDefinition::class,
        RunGlobalMethodDefinition::class,
        RunResourceMethodDefinition::class,
        DownloadFilesActionDefinition::class,
    ];
    
    public function provideActionDefinitions(BoundedContext $boundedContext, ApieContext $apieContext, bool $runtimeChecks = false): ActionDefinitionList
    {
        $actionDefinitions = [];
        foreach (self::ACTION_DEFINITION_CLASSES as $actionDefinitionClass) {
            foreach ($actionDefinitionClass::provideActionDefinitions($boundedContext, $apieContext, $runtimeChecks) as $actionDefinition) {
                $actionDefinitions[] = $actionDefinition;
            }
        }

        return new ActionDefinitionList($actionDefinitions);
    }
}
