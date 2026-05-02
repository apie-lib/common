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
use Apie\Common\ContextBuilders\ServiceContextBuilder;
use Apie\Common\Lists\ActionDefinitionList;
use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\Context\ApieContext;
use Symfony\Component\DependencyInjection\ServiceLocator;

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

    private ServiceContextBuilder $serviceContextBuilder;

    /**
     * @param ServiceLocator<object> $serviceLocator
     */
    public function __construct(ServiceLocator $serviceLocator)
    {
        $this->serviceContextBuilder = new ServiceContextBuilder($serviceLocator);
    }
    
    public function provideActionDefinitions(BoundedContext $boundedContext, ApieContext $apieContext, bool $runtimeChecks = false): ActionDefinitionList
    {
        $apieContext = $this->serviceContextBuilder->process($apieContext);
        $actionDefinitions = [];
        foreach (self::ACTION_DEFINITION_CLASSES as $actionDefinitionClass) {
            foreach ($actionDefinitionClass::provideActionDefinitions($boundedContext, $apieContext, $runtimeChecks) as $actionDefinition) {
                $actionDefinitions[] = $actionDefinition;
            }
        }

        return new ActionDefinitionList($actionDefinitions);
    }
}
