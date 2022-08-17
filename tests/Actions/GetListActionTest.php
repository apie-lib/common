<?php
namespace Apie\Tests\Common\Actions;

use Apie\Common\Actions\GetListAction;
use Apie\Common\ContextConstants;
use Apie\Core\Context\ApieContext;
use Apie\Core\Lists\ItemHashmap;
use Apie\Fixtures\Entities\UserWithAddress;
use Apie\Serializer\Lists\SerializedList;
use Apie\Common\Tests\Concerns\ProvidesApieFacade;
use PHPUnit\Framework\TestCase;

class GetListActionTest extends TestCase
{
    use ProvidesApieFacade;
    /** @test */
    public function it_can_list_items_of_a_resource()
    {
        $testItem = $this->givenAnApieFacade(GetListAction::class);
        $context = new ApieContext([
            ContextConstants::RESOURCE_NAME => UserWithAddress::class,
        ]);
        /** @var GetListAction $action */
        $action = $testItem->getAction('default', 'test', $context);
        $response = $action(
            $context,
            [
                'items_per_page' => 5
            ]
        );

        $this->assertInstanceOf(ItemHashmap::class, $response);
        $this->assertEquals(
            [
                'totalCount' => 0,
                'list' => new SerializedList([]),
                'first' => '/default/UserWithAddress?items_per_page=5',
                'last' => '/default/UserWithAddress?items_per_page=5',
            ],
            $response->toArray()
        );
    }
}
