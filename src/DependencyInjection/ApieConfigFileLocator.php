<?php
namespace Apie\Common\DependencyInjection;

use Apie\CmsApiDropdownOption\Lists\DropdownOptionList;
use Apie\Common\ApieFacade;
use Apie\Faker\ApieObjectFaker;
use Apie\RestApi\OpenApi\OpenApiGenerator;
use Apie\SchemaGenerator\ComponentsBuilderFactory;
use Apie\Serializer\Serializer;
use ReflectionClass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ApieConfigFileLocator extends FileLocator
{
    private $predefined = [
        'cms_dropdown.yaml' => [DropdownOptionList::class, '../..'],
        'common.yaml' => [ApieFacade::class, '..'],
        'faker.yaml' => [ApieObjectFaker::class, '..'],
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