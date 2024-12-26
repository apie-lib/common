<?php
namespace Apie\Tests\Common\Actions;

use Apie\Common\Actions\GetItemAction;
use Apie\Common\Tests\Concerns\ProvidesApieFacade;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\ValueObjects\DatabaseText;
use Apie\Fixtures\Entities\UserWithAddress;
use Apie\Fixtures\ValueObjects\AddressWithZipcodeCheck;
use PHPUnit\Framework\TestCase;

class GetItemActionTest extends TestCase
{
    use ProvidesApieFacade;

    #[\PHPUnit\Framework\Attributes\Test]
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
        $testItem->persistNew($user, new BoundedContextId('default'));

        $context = new ApieContext([
            ContextConstants::RESOURCE_NAME => UserWithAddress::class,
            ContextConstants::RESOURCE_ID => $user->getId()->toNative(),
            ContextConstants::BOUNDED_CONTEXT_ID => 'default',
        ]);
        /** @var GetItemAction $action */
        $action = $testItem->getAction('default', 'test', $context);
        $response = $action(
            $context,
            []
        );

        $this->assertInstanceOf(ItemHashmap::class, $response->getResultAsNativeData());
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
            $response->getResultAsNativeData()->toArray()
        );
    }
}
