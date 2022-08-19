<?php
namespace Apie\Tests\Common\Actions;

use Apie\Common\Actions\GetItemAction;
use Apie\Common\ContextConstants;
use Apie\Common\Tests\Concerns\ProvidesApieFacade;
use Apie\Core\Context\ApieContext;
use Apie\Core\Lists\ItemHashmap;
use Apie\Fixtures\Entities\UserWithAddress;
use Apie\Fixtures\ValueObjects\AddressWithZipcodeCheck;
use Apie\TextValueObjects\DatabaseText;
use PHPUnit\Framework\TestCase;

class GetItemActionTest extends TestCase
{
    use ProvidesApieFacade;

    /** @test */
    public function it_can_display_an_item()
    {
        $testItem = $this->givenAnApieFacade(GetItemAction::class);

        $user = new UserWithAddress(
            new AddressWithZipcodeCheck(
                new DatabaseText('Street'),
                new DatabaseText('42'),
                new DatabaseText('11111'),
                new DatabaseText('New York')
            )
        );
        $testItem->persistNew($user);

        $context = new ApieContext([
            ContextConstants::RESOURCE_NAME => UserWithAddress::class,
            ContextConstants::RESOURCE_ID => $user->getId()->toNative(),
        ]);
        /** @var GetItemAction $action */
        $action = $testItem->getAction('default', 'test', $context);
        $response = $action(
            $context,
            []
        );

        $this->assertInstanceOf(ItemHashmap::class, $response);
        $this->assertEquals(
            [
                'id' => $user->getId()->toNative(),
                'address' => new ItemHashmap([
                    'street' => 'Street',
                    'streetNumber' => '42',
                    'zipcode' => '11111',
                    'city' => 'New York',
                ]),
            ],
            $response->toArray()
        );
    }
}
