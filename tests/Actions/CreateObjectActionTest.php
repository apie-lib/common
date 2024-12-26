<?php
namespace Apie\Tests\Common\Actions;

use Apie\Common\Actions\CreateObjectAction;
use Apie\Common\Tests\Concerns\ProvidesApieFacade;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Core\Lists\ItemHashmap;
use Apie\Fixtures\Entities\UserWithAddress;
use PHPUnit\Framework\TestCase;

class CreateObjectActionTest extends TestCase
{
    use ProvidesApieFacade;
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_a_new_object()
    {
        $testItem = $this->givenAnApieFacade(CreateObjectAction::class);
        $context = new ApieContext([
            ContextConstants::RESOURCE_NAME => UserWithAddress::class,
            ContextConstants::BOUNDED_CONTEXT_ID => 'default',
        ]);
        /** @var CreateObjectAction $action */
        $action = $testItem->getAction('default', 'test', $context);
        $response = $action(
            $context,
            [
                'address' => [
                    'street' => 'Evergreen Terrace',
                    'streetNumber' => 742,
                    'zipcode' => '11111',
                    'city' => 'Springfield',
                ],
                'id' => '123e4567-e89b-12d3-a456-426614174000',
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
                'id' => '123e4567-e89b-12d3-a456-426614174000',
            ],
            $response->getResultAsNativeData()->toArray()
        );
    }
}
