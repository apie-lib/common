<?php
namespace Apie\Common\MenuStructure;

use Apie\Core\Lists\StringList;
use Apie\Core\Translator\ValueObjects\MenuHeader;

class MenuBuilder
{
    private MenuNode $root;

    public function __construct(private readonly string $prefix = '')
    {
        $this->root = new MenuNode(MenuHeader::createRoot(path: rtrim($this->prefix, '.-')));
    }

    public function getRoot(): MenuNode
    {
        return $this->root;
    }

    public function addLeaf(string|StringList $tag, MenuNode $leaf): self
    {
        if (is_string($tag)) {
            $tag = new StringList([$tag]);
        }
        $parent = null;
        $current = $this->root;
        foreach ($tag as $part) {
            $parent = $current;
            if ($part) {
                if (!isset($current->children[$part])) {
                    $children = $current->children;
                    $children[$part] = new MenuNode($current->name->createChildNode($part));
                    $current->children = $children;
                }
                $current = $current->children[$part];
            }
        }
        if ($parent === null) {
            $this->root = $leaf;
            return $this;
        }
        $current->name = $leaf->name;
        $current->route = $leaf->route;
        $current->icon = $leaf->icon;
        $current->allowed = $leaf->allowed;

        return $this;
    }
}
