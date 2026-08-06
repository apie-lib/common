<?php
namespace Apie\Tests\Common\MenuStructure;

use Apie\Common\MenuStructure\MenuBuilder;
use Apie\Common\MenuStructure\MenuNode;
use Apie\Common\MenuStructure\MenuNodeChildren;
use Apie\Core\Lists\StringList;
use Apie\Core\Translator\ValueObjects\MenuHeader;
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
            new MenuNode(MenuHeader::createRoot()),
            $testItem->getRoot()
        );
        $testItem->addLeaf('test', new MenuNode(MenuHeader::createRoot()->createChildNode('test')));
        $this->assertEquals(
            new MenuNode(
                MenuHeader::createRoot(),
                null,
                null,
                new MenuNodeChildren([
                    'test' => new MenuNode(MenuHeader::createRoot()->createChildNode('test')),
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
        $testItem->addLeaf(
            new StringList(['sub', 'sub2']),
            new MenuNode(MenuHeader::createRoot()->createChildNode('sub')->createChildNode('sub2'))
        );
        $this->assertEquals(
            new MenuNode(
                MenuHeader::createRoot(),
                null,
                null,
                new MenuNodeChildren([
                    'test' => new MenuNode(MenuHeader::createRoot()->createChildNode('test')),
                    'sub' => new MenuNode(
                        MenuHeader::createRoot()->createChildNode('sub'),
                        null,
                        null,
                        new MenuNodeChildren([
                            'sub2' => new MenuNode(
                                MenuHeader::createRoot()->createChildNode('sub')->createChildNode('sub2'),
                            ),
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
        $testItem->addLeaf(new StringList(['sub']), new MenuNode(MenuHeader::createRoot()->createChildNode('sub'), 'url', 'icon-email'));

        $this->assertEquals(
            new MenuNode(
                MenuHeader::createRoot(),
                null,
                null,
                new MenuNodeChildren([
                    'test' => new MenuNode(MenuHeader::createRoot()->createChildNode('test')),
                    'sub' => new MenuNode(
                        MenuHeader::createRoot()->createChildNode('sub'),
                        'url',
                        'icon-email',
                        new MenuNodeChildren([
                            'sub2' => new MenuNode(MenuHeader::createRoot()->createChildNode('sub')->createChildNode('sub2')),
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
        $testItem->addLeaf('test', new MenuNode(MenuHeader::createRoot()->createChildNode('test'), 'url-2'));

        $this->assertEquals(
            new MenuNode(
                MenuHeader::createRoot(),
                null,
                null,
                new MenuNodeChildren([
                    'test' => new MenuNode(MenuHeader::createRoot()->createChildNode('test'), 'url-2'),
                    'sub' => new MenuNode(
                        MenuHeader::createRoot()->createChildNode('sub'),
                        'url',
                        'icon-email',
                        new MenuNodeChildren([
                            'sub2' => new MenuNode(
                                MenuHeader::createRoot()->createChildNode('sub')->createChildNode('sub2')
                            ),
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
        $testItem->addLeaf('test', new MenuNode(MenuHeader::createRoot()->createChildNode('test'), 'url-2'));

        $this->assertEquals(
            new MenuNode(
                MenuHeader::createRoot(),
                null,
                null,
                new MenuNodeChildren([
                    'test' => new MenuNode(MenuHeader::createRoot()->createChildNode('test'), 'url-2'),
                    'sub' => new MenuNode(
                        MenuHeader::createRoot()->createChildNode('sub'),
                        'url',
                        'icon-email',
                        new MenuNodeChildren([
                            'sub2' => new MenuNode(MenuHeader::createRoot()->createChildNode('sub')->createChildNode('sub2')),
                        ])
                    ),
                ])
            ),
            $testItem->getRoot()
        );
    }
}
