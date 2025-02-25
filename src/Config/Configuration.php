<?php
namespace Apie\Common\Config;

use Apie\DoctrineEntityDatalayer\IndexStrategy\DirectIndexStrategy;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration
 */
abstract class Configuration implements ConfigurationInterface
{
    private const ENABLE_CONFIGS = [
        'enable_common_plugin' => 'Apie\ApieCommonPlugin\ApieCommonPlugin',
        'enable_cms' => 'Apie\Cms\RouteDefinitions\CmsRouteDefinitionProvider',
        'enable_cms_dropdown' => 'Apie\CmsApiDropdownOption\RouteDefinitions\DropdownOptionsForExistingObjectRouteDefinition',
        'enable_doctrine_entity_converter' => 'Apie\DoctrineEntityConverter\OrmBuilder',
        'enable_doctrine_entity_datalayer' => 'Apie\DoctrineEntityDatalayer\DoctrineEntityDatalayer',
        'enable_faker' => 'Apie\Faker\ApieObjectFaker',
        'enable_maker' => 'Apie\Maker\Utils',
        'enable_rest_api' => 'Apie\RestApi\OpenApi\OpenApiGenerator',
        'enable_console' => 'Apie\Console\ConsoleCommandFactory',
        'enable_twig_template_layout_renderer' => 'Apie\TwigTemplateLayoutRenderer\TwigRenderer',
    ];

    abstract protected function addCmsOptions(ArrayNodeDefinition $arrayNode): void;

    abstract protected function addApiOptions(ArrayNodeDefinition $arrayNode): void;

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('apie');

        $children = $treeBuilder->getRootNode()->children();
        $children->booleanNode('enable_core')->defaultValue(true)->end();
        $children->scalarNode('encryption_key')->end();
        $cmsConfig = $children->arrayNode('cms');
        $cmsConfig->children()
          ->scalarNode('base_url')->defaultValue('/cms')->end()
          ->arrayNode('asset_folders')->scalarPrototype()->end()
        ->end();
        $this->addCmsOptions($cmsConfig);
        $apiConfig = $children->arrayNode('rest_api');
        $apiConfig->children()
          ->scalarNode('base_url')->defaultValue('/api')->end()
        ->end();
        $this->addApiOptions($apiConfig);
        $children->arrayNode('datalayers')
                ->children()
                    ->scalarNode('default_datalayer')->isRequired()->end()
                    ->arrayNode('context_mapping')
                        ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->isRequired()
                            ->children()
                                ->scalarNode('default_datalayer')->isRequired()->end()
                                ->arrayNode('entity_mapping')
                                    ->useAttributeAsKey('class')
                                    ->scalarPrototype()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('doctrine')
                ->children()
                    ->arrayNode('indexing')
                        ->children()
                            ->enumNode('type')->values(['direct', 'late', 'background', 'custom'])->defaultValue('direct')->end()
                            ->scalarNode('service')->defaultValue(DirectIndexStrategy::class)->end()
                        ->end()
                    ->end()
                    ->scalarNode('build_once')->defaultValue(false)->end()
                    ->scalarNode('run_migrations')->defaultValue(true)->end()
                    ->arrayNode('connection_params')
                      ->defaultValue(['driver' => 'pdo_sqlite'])
                      ->useAttributeAsKey('class')
                      ->scalarPrototype()
                      ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('storage')
              ->arrayPrototype()
                ->children()
                  ->scalarNode('class')->isRequired()->end()
                  ->arrayNode('options')->defaultValue([])
                    ->scalarPrototype()
                    ->end()
                  ->end()
                ->end()
              ->end()
            ->end()
            ->arrayNode('maker')
                ->children()
                  ->scalarNode('target_path')->defaultValue(false)->end()
                  ->scalarNode('target_namespace')->defaultValue('App\Apie')->end()
                ->end()
            ->end()
            ->arrayNode('bounded_contexts')
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('entities_folder')->isRequired()->end()
                        ->scalarNode('entities_namespace')->isRequired()->end()
                        ->scalarNode('actions_folder')->isRequired()->end()
                        ->scalarNode('actions_namespace')->isRequired()->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('scan_bounded_contexts')
                ->children()
                    ->scalarNode('search_path')->end()
                    ->scalarNode('search_namespace')->end()
                ->end()
            ->end();
        $childNode = $treeBuilder->getRootNode()->children();
        foreach (self::ENABLE_CONFIGS as $configKey => $classNameToExist) {
            $childNode->booleanNode($configKey)->defaultValue(class_exists($classNameToExist));
        }
        return $treeBuilder;
    }
}
