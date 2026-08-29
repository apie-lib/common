<?php
namespace Apie\Tests\Common\MenuStructure;

use Apie\Common\MenuStructure\MenuNode;
use Apie\Common\MenuStructure\MenuNodeChildren;
use Apie\Core\Context\ApieContext;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Translator\ValueObjects\MenuHeader;
use Apie\Serializer\Lists\SerializedHashmap;
use Apie\Serializer\Serializer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MenuNodeTest extends TestCase
{
    #[Test]
    public function it_can_be_created_and_serialized()
    {
        $testItem = new MenuNode(
            MenuHeader::createRoot(),
            '/dashboard',
            'mui:home',
        );
        $serializer = Serializer::create();
        $actual = $serializer->normalize($testItem, new ApieContext());
        $expected = new ItemHashmap(
            [
                'id' => 'menu_header',
                'children' => new SerializedHashmap(),
                'name' => 'apie.menu.header.unauthenticated',
                'route' => '/dashboard',
                'icon' => 'mui:home',
                'allowed' => true,
            ]
        );
        $this->assertEquals($expected, $actual);
    }

    #[Test]
    public function it_can_be_prune_unused_trees()
    {
        $root = MenuHeader::createRoot();
        $test4 = $root->createChildNode('test4');
        $testItem = new MenuNode(
            $root,
            '/dashboard',
            'mui:home',
            new MenuNodeChildren([
                'test' => new MenuNode(
                    $root->createChildNode('test')
                ),
                'test2' => new MenuNode(
                    $root->createChildNode('test2'),
                    '/dashboard/test2'
                ),
                'test3' => new MenuNode(
                    $root->createChildNode('test3'),
                    '/dashboard/test3',
                    allowed: false
                ),
                'test4' => new MenuNode(
                    $test4,
                    children: new MenuNodeChildren([
                        'blah' => new MenuNode(
                            $test4->createChildNode('blah'),
                            '/dashboard/test4/blah'
                        ),
                        'blah2' => new MenuNode(
                            $test4->createChildNode('blah2'),
                            '/dashboard/test4/blah',
                            allowed: false
                        )
                    ])
                )
            ])
        );
        $testItem->prune();

        $serializer = Serializer::create();
        $actual = $serializer->normalize($testItem, new ApieContext());
        
        $expected = new ItemHashmap(
            [
                'id' => 'menu_header',
                'children' => new SerializedHashmap([
                    'test2' => new SerializedHashmap([
                        'id' => 'menu_test2_header',
                        'children' => new SerializedHashmap(),
                        'name' => 'apie.menu.test2.header.unauthenticated',
                        'route' => '/dashboard/test2',
                        'icon' => null,
                        'allowed' => true,
                    ]),
                    'test4' => new SerializedHashmap([
                        'id' => 'menu_test4_header',
                        'children' => new SerializedHashmap([
                            'blah' => new SerializedHashmap([
                                "id" => "menu_test4_blah_header",
                                "children" => new SerializedHashmap(),
                                "name" => "apie.menu.test4.blah.header.unauthenticated",
                                "route" => "/dashboard/test4/blah",
                                "icon" => null,
                                "allowed" => true
                            ]),
                        ]),
                        'name' => 'apie.menu.test4.header.unauthenticated',
                        'route' => null,
                        'icon' => null,
                        'allowed' => true,
                    ])
                ]),
                'name' => 'apie.menu.header.unauthenticated',
                'route' => '/dashboard',
                'icon' => 'mui:home',
                'allowed' => true,
            ]
        );
        $this->assertEquals($expected, $actual);
    }
}
