<?php
namespace Apie\Common\DependencyInjection;

use Apie\Cms\RouteDefinitions\AbstractCmsRouteDefinition;
use Apie\CmsApiDropdownOption\Lists\DropdownOptionList;
use Apie\Common\ApieFacade;
use Apie\Console\ConsoleCommandFactory;
use Apie\Core\Context\ApieContext;
use Apie\Faker\ApieObjectFaker;
use Apie\HtmlBuilders\FormBuildContext;
use Apie\RestApi\OpenApi\OpenApiGenerator;
use Apie\SchemaGenerator\ComponentsBuilderFactory;
use Apie\Serializer\Serializer;
use ReflectionClass;
use Symfony\Component\Config\FileLocator;

class ApieConfigFileLocator extends FileLocator
{
    /**
     * @var array<string, array{class-string<object>, string}>
     */
    private array $predefined = [
        'cms.yaml' => [AbstractCmsRouteDefinition::class, '../..'],
        'cms_dropdown.yaml' => [DropdownOptionList::class, '../..'],
        'common.yaml' => [ApieFacade::class, '..'],
        'console.yaml' => [ConsoleCommandFactory::class, '..'],
        'core.yaml' => [ApieContext::class, '../..'],
        'faker.yaml' => [ApieObjectFaker::class, '..'],
        'html_builders.yaml' => [FormBuildContext::class, '..'],
        'rest_api.yaml' => [OpenApiGenerator::class, '../..'],
        'serializer.yaml' => [Serializer::class, '..'],
        'schema_generator.yaml' => [ComponentsBuilderFactory::class, '..']
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
}
