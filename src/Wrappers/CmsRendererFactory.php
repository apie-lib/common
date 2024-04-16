<?php
namespace Apie\Common\Wrappers;

use Apie\CmsLayoutGraphite\GraphiteDesignSystemLayout;
use Apie\Core\Context\ApieContext;
use Apie\HtmlBuilders\Assets\AssetManager;
use Apie\HtmlBuilders\Interfaces\ComponentInterface;
use Apie\HtmlBuilders\Interfaces\ComponentRendererInterface;

final class CmsRendererFactory
{
    private function __construct()
    {
    }

    public static function createRenderer(?AssetManager $assetManager): ComponentRendererInterface
    {
        if (class_exists(GraphiteDesignSystemLayout::class)) {
            return GraphiteDesignSystemLayout::createRenderer($assetManager);
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
