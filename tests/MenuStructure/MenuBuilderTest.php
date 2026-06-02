<?php
namespace Apie\Tests\Common\MenuStructure;

use Apie\Common\MenuStructure\MenuBuilder;
use Apie\Common\MenuStructure\MenuNode;
use Apie\Common\MenuStructure\MenuNodeChildren;
use Apie\Core\Lists\StringList;
use PHPUnit\Framework\Attributes\DependsUsingDeepClone;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MenuBuilderTest extends TestCase
{
    #[Test]
    public function it_can_create_a_menu()
    {
        $testItem = new MenuBuilder();
        $this->assertEquals(
            new MenuNode('', ''),
            $testItem->getRoot()
        );
        $testItem->addLeaf('test', new MenuNode('id', 'name'));
        $this->assertEquals(
            new MenuNode(
                '',
                '',
                null,
                null,
                new MenuNodeChildren([
                    'test' => new MenuNode('id', 'name'),
                ])
            ),
            $testItem->getRoot()
        );
        return $testItem;
    }

    #[Test]
    #[DependsUsingDeepClone('it_can_create_a_menu')]
    public function it_can_add_a_sub_menu_in_submenu(MenuBuilder $testItem)
    {
        $testItem->addLeaf(new StringList(['sub', 'sub2']), new MenuNode('sub-id', 'Sub menu'));
        $this->assertEquals(
            new MenuNode(
                '',
                '',
                null,
                null,
                new MenuNodeChildren([
                    'test' => new MenuNode('id', 'name'),
                    'sub' => new MenuNode(
                        'sub',
                        '',
                        null,
                        null,
                        new MenuNodeChildren([
                            'sub2' => new MenuNode('sub-id', 'Sub menu'),
                        ])
                    ),
                ])
            ),
            $testItem->getRoot()
        );

        return $testItem;
    }

    #[Test]
    #[DependsUsingDeepClone('it_can_add_a_sub_menu_in_submenu')]
    public function it_can_replace_a_node_with_a_url(MenuBuilder $testItem)
    {
        $testItem->addLeaf(new StringList(['sub']), new MenuNode('sub', 'Sub menu', 'url', 'icon-email'));

        $this->assertEquals(
            new MenuNode(
                '',
                '',
                null,
                null,
                new MenuNodeChildren([
                    'test' => new MenuNode('id', 'name'),
                    'sub' => new MenuNode(
                        'sub',
                        'Sub menu',
                        'url',
                        'icon-email',
                        new MenuNodeChildren([
                            'sub2' => new MenuNode('sub-id', 'Sub menu'),
                        ])
                    ),
                ])
            ),
            $testItem->getRoot()
        );

        return $testItem;
    }

    #[Test]
    #[DependsUsingDeepClone('it_can_replace_a_node_with_a_url')]
    public function it_can_replace_a_node_if_id_matches(MenuBuilder $testItem)
    {
        $testItem->addLeaf('test', new MenuNode('id', 'new name', 'url-2'));

        $this->assertEquals(
            new MenuNode(
                '',
                '',
                null,
                null,
                new MenuNodeChildren([
                    'test' => new MenuNode('id', 'new name', 'url-2'),
                    'sub' => new MenuNode(
                        'sub',
                        'Sub menu',
                        'url',
                        'icon-email',
                        new MenuNodeChildren([
                            'sub2' => new MenuNode('sub-id', 'Sub menu'),
                        ])
                    ),
                ])
            ),
            $testItem->getRoot()
        );
    }

    #[Test]
    #[DependsUsingDeepClone('it_can_replace_a_node_with_a_url')]
    public function it_does_not_replace_a_node_if_id_does_not_match(MenuBuilder $testItem)
    {
        $testItem->addLeaf('test', new MenuNode('other-id', 'new name', 'url-2'));

        $this->assertEquals(
            new MenuNode(
                '',
                '',
                null,
                null,
                new MenuNodeChildren([
                    'test' => new MenuNode('id', 'name', null),
                    'test-0' => new MenuNode('other-id', 'new name', 'url-2'),
                    'sub' => new MenuNode(
                        'sub',
                        'Sub menu',
                        'url',
                        'icon-email',
                        new MenuNodeChildren([
                            'sub2' => new MenuNode('sub-id', 'Sub menu'),
                        ])
                    ),
                ])
            ),
            $testItem->getRoot()
        );
    }
}
