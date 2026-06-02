<?php
namespace Apie\Common\MenuStructure;

use Apie\Core\Lists\StringList;

class MenuBuilder
{
    private MenuNode $root;

    public function __construct(private readonly string $prefix = '')
    {
        $this->root = new MenuNode(rtrim($this->prefix, '.-'), '');
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
            if (!isset($current->children[$part])) {
                $children = $current->children;
                $children[$part] = new MenuNode($this->prefix . $part, '');
                $current->children = $children;
            }
            $current = $current->children[$part];
            $lastKey = $part;
        }
        if ($parent === null) {
            $this->root = $leaf;
            return $this;
        }
        if ($current->children->count()) {
            $current->name = $leaf->name;
            $current->route = $leaf->route;
            $current->icon = $leaf->icon;
        } elseif ($current->name && $current->id !== $leaf->id) {
            for ($i = 0; $i < 1000; $i++) {
                if (!isset($parent->children[$lastKey . '-' . $i])) {
                    $parent->children[$lastKey . '-' . $i] = $leaf;
                    return $this;
                }
            }
            throw new \LogicException(sprintf('There is already a leaf with id %s', $current->id));
        } else {
            $parent->children[$lastKey] = $leaf;
        }

        return $this;
    }
}
