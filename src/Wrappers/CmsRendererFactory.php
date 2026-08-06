<?php
namespace Apie\Common\Wrappers;

use Apie\CmsLayoutGraphite\GraphiteDesignSystemLayout;
use Apie\CmsLayoutIonic\IonicDesignSystemLayout;
use Apie\CmsLayoutUgly\UglyDesignSystemLayout;
use Apie\Core\Context\ApieContext;
use Apie\HtmlBuilders\Assets\AssetManager;
use Apie\HtmlBuilders\Interfaces\ComponentInterface;
use Apie\HtmlBuilders\Interfaces\ComponentRendererInterface;
use Symfony\Component\DependencyInjection\Container;
use Twig\RuntimeLoader\ContainerRuntimeLoader;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

final class CmsRendererFactory
{
    private function __construct()
    {
    }

    private static function createFallbackLoader(): RuntimeLoaderInterface
    {
        $container = new Container();
        //$container->set()
        return new ContainerRuntimeLoader($container);
    }

    public static function createRenderer(
        ?RuntimeLoaderInterface $runtimeLoader,
        ?AssetManager $assetManager,
    ): ComponentRendererInterface {
        if ($runtimeLoader === null) {
            $runtimeLoader = self::createFallbackLoader();
        }
        if (class_exists(IonicDesignSystemLayout::class)) {
            return IonicDesignSystemLayout::createRenderer(
                $runtimeLoader,
                $assetManager,
            );
        }
        if (class_exists(GraphiteDesignSystemLayout::class)) {
            return GraphiteDesignSystemLayout::createRenderer(
                $runtimeLoader,
                $assetManager,
            );
        }
        if (class_exists(UglyDesignSystemLayout::class)) {
            return UglyDesignSystemLayout::createRenderer(
                $runtimeLoader,
                $assetManager,
            );
        }
        // fallback is just a message displaying you need to install a cms renderer package.
        $contents = file_get_contents(__DIR__ . '/../../resources/html/install-instructions-cms-renderer.html');
        return new class($contents) implements ComponentRendererInterface {
            public function __construct(private string $contents)
            {
            }

            public function render(ComponentInterface $componentInterface, ApieContext $apieContext): string
            {
                return $this->contents;
            }
        };
    }
}
