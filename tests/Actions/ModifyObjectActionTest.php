<?php
namespace Apie\Tests\Common\Actions;

use Apie\Common\Actions\ModifyObjectAction;
use Apie\Common\Exceptions\EntityEtagConflictException;
use Apie\Common\Tests\Concerns\ProvidesApieFacade;
use Apie\Core\Actions\ApieFacadeInterface;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Core\Datalayers\ApieDatalayer;
use Apie\Core\Datalayers\DataLayerMetadata;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\ValueObjects\DatabaseText;
use Apie\Fixtures\Entities\UserWithAddress;
use Apie\Fixtures\ValueObjects\AddressWithZipcodeCheck;
use DateTimeImmutable;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;

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
            LockFactory::class => new LockFactory(new FlockStore()),
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_rejects_a_patch_with_a_stale_if_match_header(): void
    {
        $resource = new UserWithAddress(
            new AddressWithZipcodeCheck(
                new DatabaseText('Evergreen Terrace'),
                new DatabaseText('742'),
                new DatabaseText('11111'),
                new DatabaseText('Springfield')
            )
        );
        $apieFacade = $this->createMock(ApieFacadeInterface::class);
        $apieFacade->expects($this->once())
            ->method('find')
            ->willReturn($resource);
        $apieFacade->expects($this->never())->method('denormalizeOnExistingObject');
        $apieFacade->expects($this->never())->method('persistExisting');

        $dataLayer = $this->createMock(ApieDatalayer::class);
        $dataLayer->expects($this->once())
            ->method('getMetadata')
            ->willReturn(new DataLayerMetadata(
                new DateTimeImmutable('2025-01-01T00:00:00+00:00'),
                new DateTimeImmutable('2025-02-01T00:00:00+00:00'),
                '1.0.0',
                '1.0.1'
            ));
        $request = new ServerRequest(
            'PATCH',
            '/UserWithAddress/42',
            ['If-Match' => '"stale-etag"']
        );
        $context = new ApieContext([
            ContextConstants::RESOURCE_NAME => UserWithAddress::class,
            ContextConstants::RESOURCE_ID => (string) $resource->getId()->toNative(),
            ContextConstants::BOUNDED_CONTEXT_ID => 'default',
            ContextConstants::REST_API => true,
            ServerRequestInterface::class => $request,
            ApieDatalayer::class => $dataLayer,
            LockFactory::class => new LockFactory(new FlockStore()),
        ]);

        $response = (new ModifyObjectAction($apieFacade))($context, []);

        $this->assertSame(409, $response->getStatusCode());
        $this->assertInstanceOf(EntityEtagConflictException::class, $response->error);
    }
}
