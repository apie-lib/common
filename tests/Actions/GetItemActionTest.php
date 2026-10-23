<?php
namespace Apie\Tests\Common\Actions;

use Apie\Common\Actions\GetItemAction;
use Apie\Common\Exceptions\EntityNotModifiedException;
use Apie\Common\Tests\Concerns\ProvidesApieFacade;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Core\Datalayers\ApieDatalayer;
use Apie\Core\Datalayers\DataLayerMetadata;
use Apie\Core\Identifiers\IdentifierInterface;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\ValueObjects\DatabaseText;
use Apie\Fixtures\Entities\UserWithAddress;
use Apie\Fixtures\Entities\UserWithAutoincrementKey;
use Apie\Fixtures\ValueObjects\AddressWithZipcodeCheck;
use DateTimeImmutable;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\NullStore;

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
            LockFactory::class => new LockFactory(new NullStore()),
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_not_modified_before_finding_the_entity_when_if_none_match_matches(): void
    {
        $dataLayer = $this->createMock(ApieDatalayer::class);
        $metadata = new DataLayerMetadata(
            new DateTimeImmutable('2025-01-01T00:00:00+00:00'),
            new DateTimeImmutable('2025-02-01T12:34:56.123456+00:00'),
            '1.0.0',
            '1.0.1'
        );
        $dataLayer->expects($this->once())
            ->method('getMetadata')
            ->with(
                $this->isInstanceOf(IdentifierInterface::class),
                $this->callback(static fn (BoundedContextId $id): bool => $id->toNative() === 'default')
            )
            ->willReturn($metadata);
        $apieFacade = $this->createMock(\Apie\Core\Actions\ApieFacadeInterface::class);
        $apieFacade->expects($this->never())->method('find');
        $action = new GetItemAction($apieFacade);
        $context = new ApieContext([
            ContextConstants::RESOURCE_NAME => UserWithAutoincrementKey::class,
            ContextConstants::RESOURCE_ID => '42',
            ContextConstants::BOUNDED_CONTEXT_ID => 'default',
            ContextConstants::REST_API => true,
            ApieDatalayer::class => $dataLayer,
            ServerRequestInterface::class => (new ServerRequest('GET', '/UserWithAutoincrementKey/42'))
                ->withHeader('If-None-Match', '"' . $metadata->etag . '"'),
            LockFactory::class => new LockFactory(new NullStore()),
        ]);

        try {
            $action($context, []);
            $this->fail('Expected a not-modified exception.');
        } catch (EntityNotModifiedException $exception) {
            $this->assertSame(304, $exception->getStatusCode());
            $this->assertSame('"' . $metadata->etag . '"', $exception->etag);
        }
    }
}
