<?php
namespace Apie\Common\MenuStructure;

use Apie\Core\Dto\DtoInterface;
use Apie\Core\Translator\ValueObjects\MenuHeader;

final class MenuNode implements DtoInterface
{
    public MenuNodeChildren $children;
    public function __construct(
        public readonly string $id,
        public MenuHeader|string $name,
        public ?string $route = null,
        public ?string $icon = null,
        ?MenuNodeChildren $children = null,
    ) {
        $this->children = $children ?? new MenuNodeChildren();
    }
}
