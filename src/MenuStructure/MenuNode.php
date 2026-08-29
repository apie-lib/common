<?php
namespace Apie\Common\MenuStructure;

use Apie\Core\Dto\DtoInterface;
use Apie\Core\Translator\ValueObjects\MenuHeader;

final class MenuNode implements DtoInterface
{
    public readonly string $id;
    public MenuNodeChildren $children;
    public function __construct(
        public MenuHeader $name,
        public ?string $route = null,
        public ?string $icon = null,
        ?MenuNodeChildren $children = null,
        public bool $allowed = true,
    ) {
        $this->id = $name->asId();
        $this->children = $children ?? new MenuNodeChildren();
    }

    public function prune(): self
    {
        $newChildren = [];
        foreach ($this->children as $key => $child) {
            $child->prune();
            if (($child->allowed && $child->route !== null) || $child->children->count() > 0) {
                $newChildren[$key] = $child;
            }
        }
        $this->children = new MenuNodeChildren($newChildren);

        return $this;
    }
}
