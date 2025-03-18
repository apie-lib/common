<?php
namespace Apie\Tests\Common\Actions;

use Apie\Common\Actions\ModifyObjectAction;
use Apie\Common\Tests\Concerns\ProvidesApieFacade;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\ValueObjects\DatabaseText;
use Apie\Fixtures\Entities\UserWithAddress;
use Apie\Fixtures\ValueObjects\AddressWithZipcodeCheck;
use PHPUnit\Framework\TestCase;

class ModifyObjectActionTest extends TestCase
{
    use ProvidesApieFacade;
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_modify_an_new_object()
    {
        $testItem = $this->givenAnApieFacade(ModifyObjectAction::class);
        $userWithAddress = new UserWithAddress(
            new AddressWithZipcodeCheck(
                new DatabaseText('Evergreen Terrace'),
                new DatabaseText('742'),
                new DatabaseText('11111'),
                new DatabaseText('Springfield')
            )
        );
        $testItem->persistNew($userWithAddress, new BoundedContextId('default'));
        $context = new ApieContext([
            ContextConstants::RESOURCE_NAME => UserWithAddress::class,
            'id' => $userWithAddress->getId()->toNative(),
            ContextConstants::BOUNDED_CONTEXT_ID => 'default',
        ]);
        /** @var ModifyObjectAction $action */
        $action = $testItem->getAction('default', 'test', $context);
        $response = $action(
            $context,
            [
                'password' => 'Str@nGpAs5',
            ]
        );

        $this->assertInstanceOf(ItemHashmap::class, $response->getResultAsNativeData());
        $this->assertEquals(
            [
                'address' => new ItemHashmap([
                    'street' => 'Evergreen Terrace',
                    'streetNumber' => 742,
                    'zipcode' => '11111',
                    'city' => 'Springfield',
                ]),
                'id' => $userWithAddress->getId()->toNative(),
            ],
            $response->getResultAsNativeData()->toArray()
        );
    }
}
