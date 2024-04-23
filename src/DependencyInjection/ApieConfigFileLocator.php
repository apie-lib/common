<?php
namespace Apie\Common\DependencyInjection;

use Apie\ApieCommonPlugin\ApieCommonPlugin;
use Apie\Cms\RouteDefinitions\AbstractCmsRouteDefinition;
use Apie\CmsApiDropdownOption\Lists\DropdownOptionList;
use Apie\Common\ApieFacade;
use Apie\Console\ConsoleCommandFactory;
use Apie\Core\Context\ApieContext;
use Apie\DoctrineEntityConverter\Factories\PersistenceLayerFactory;
use Apie\DoctrineEntityDatalayer\DoctrineEntityDatalayer;
use Apie\Faker\ApieObjectFaker;
use Apie\HtmlBuilders\FormBuildContext;
use Apie\Maker\Utils;
use Apie\RestApi\OpenApi\OpenApiGenerator;
use Apie\SchemaGenerator\ComponentsBuilderFactory;
use Apie\Serializer\Serializer;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Config\FileLocator;

class ApieConfigFileLocator extends FileLocator
{
    /**
     * @var array<string, array{class-string<object>, string, class-string<object>}>
     */
    private array $predefined = [
        'apie_common_plugin.yaml' => [ApieCommonPlugin::class, '..', 'Apie\\ApieCommonPlugin\\GeneratedApieCommonPluginServiceProvider'],
        'cms.yaml' => [AbstractCmsRouteDefinition::class, '../..', 'Apie\\Cms\\CmsServiceProvider'],
        'cms_dropdown.yaml' => [DropdownOptionList::class, '../..', 'Apie\\CmsApiDropdownOption\\CmsDropdownServiceProvider'],
        'common.yaml' => [ApieFacade::class, '..', 'Apie\\Common\\CommonServiceProvider'],
        'console.yaml' => [ConsoleCommandFactory::class, '..', 'Apie\\Console\\ConsoleServiceProvider'],
        'core.yaml' => [ApieContext::class, '../..', 'Apie\\Core\\CoreServiceProvider'],
        'doctrine_entity_converter.yaml' => [PersistenceLayerFactory::class, '../..', 'Apie\\DoctrineEntityConverter\\DoctrineEntityConverterProvider'],
        'doctrine_entity_datalayer.yaml' => [DoctrineEntityDatalayer::class, '..', 'Apie\\DoctrineEntityDatalayer\\DoctrineEntityDatalayerServiceProvider'],
        'faker.yaml' => [ApieObjectFaker::class, '..', 'Apie\\Faker\\FakerServiceProvider'],
        'html_builders.yaml' => [FormBuildContext::class, '..', 'Apie\\HtmlBuilders\\HtmlBuilderServiceProvider'],
        'maker.yaml' => [Utils::class, '..', 'Apie\\Maker\\MakerServiceProvider'],
        'rest_api.yaml' => [OpenApiGenerator::class, '../..', 'Apie\\RestApi\\RestApiServiceProvider'],
        'serializer.yaml' => [Serializer::class, '..', 'Apie\\Serializer\\SerializerServiceProvider'],
        'schema_generator.yaml' => [ComponentsBuilderFactory::class, '..', 'Apie\\SchemaGenerator\\SchemaGeneratorServiceProvider']
    ];

    public function __construct(string|array $paths = [])
    {
        $paths = (array) $paths;
        parent::__construct($paths);
    }

    /**
     * @return string|string[]
     */
    public function locate(string $name, string $currentPath = null, bool $first = true): array|string
    {
        if ($currentPath !== null || !isset($this->predefined[$name])) {
            return parent::locate($name, $currentPath, $first);
        }
        $config = $this->predefined[$name];
        $refl = new ReflectionClass($config[0]);
        return dirname(realpath($refl->getFileName())) . DIRECTORY_SEPARATOR . $config[1] . DIRECTORY_SEPARATOR . $name;
    }

    /**
     * @return array<int, array{string, class-string<object>}>
     */
    public function getAllPaths(): array
    {
        $result = [];
        foreach (array_keys($this->predefined) as $name) {
            try {
                $result[] = [(string) $this->locate($name), $this->predefined[$name][2]];
            } catch (ReflectionException) {
            }
        }
        return $result;
    }
}
