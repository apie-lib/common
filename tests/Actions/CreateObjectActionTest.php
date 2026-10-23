<?php
namespace Apie\Tests\Common\Actions;

use Apie\Common\Actions\CreateObjectAction;
use Apie\Common\Exceptions\EntityEtagConflictException;
use Apie\Common\Interfaces\ApieFacadeInterface;
use Apie\Common\Tests\Concerns\ProvidesApieFacade;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Core\Datalayers\ApieDatalayer;
use Apie\Core\Datalayers\DataLayerMetadata;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Enums\RequestMethod;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\PermissionList;
use Apie\Core\Permissions\RequiresPermissionsInterface;
use Apie\Fixtures\Entities\UserWithAddress;
use Apie\Fixtures\Identifiers\UserWithAddressIdentifier;
use DateTimeImmutable;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\NullStore;

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
            LockFactory::class => new LockFactory(new NullStore()),
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_a_new_object_with_put(): void
    {
        $testItem = $this->givenAnApieFacade(CreateObjectAction::class);
        $context = new ApieContext([
            ContextConstants::RESOURCE_NAME => UserWithAddress::class,
            ContextConstants::BOUNDED_CONTEXT_ID => 'default',
            ContextConstants::REST_API => true,
            RequestMethod::class => RequestMethod::PUT,
            LockFactory::class => new LockFactory(new NullStore()),
        ]);
        /** @var CreateObjectAction $action */
        $action = $testItem->getAction('default', 'test', $context);
        $response = $action($context, [
            'address' => [
                'street' => 'Evergreen Terrace',
                'streetNumber' => 742,
                'zipcode' => '11111',
                'city' => 'Springfield',
            ],
            'id' => '123e4567-e89b-12d3-a456-426614174000',
        ]);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame(
            '123e4567-e89b-12d3-a456-426614174000',
            $response->getResultAsNativeData()['id']
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_replaces_an_existing_object_with_put_when_accessible(): void
    {
        $testItem = $this->givenAnApieFacade(CreateObjectAction::class);
        $postContext = new ApieContext([
            ContextConstants::RESOURCE_NAME => UserWithAddress::class,
            ContextConstants::BOUNDED_CONTEXT_ID => 'default',
            LockFactory::class => new LockFactory(new NullStore()),
        ]);
        /** @var CreateObjectAction $action */
        $action = $testItem->getAction('default', 'test', $postContext);
        $original = $action($postContext, [
            'address' => [
                'street' => 'Evergreen Terrace',
                'streetNumber' => 742,
                'zipcode' => '11111',
                'city' => 'Springfield',
            ],
            'id' => '123e4567-e89b-12d3-a456-426614174000',
        ]);
        $putContext = $postContext
            ->withContext(RequestMethod::class, RequestMethod::PUT)
            ->withContext(LockFactory::class, new LockFactory(new NullStore()));
        $updated = $action($putContext, [
            'address' => [
                'street' => 'Evergreen Terrace',
                'streetNumber' => 744,
                'zipcode' => '11111',
                'city' => 'Springfield',
            ],
            'id' => '123e4567-e89b-12d3-a456-426614174000',
        ]);

        $this->assertSame(201, $original->getStatusCode());
        $this->assertSame(201, $updated->getStatusCode());
        $this->assertSame('744', $updated->getResultAsNativeData()['address']['streetNumber']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_conflict_for_a_stale_if_match_on_put(): void
    {
        $identifier = UserWithAddressIdentifier::fromNative('123e4567-e89b-12d3-a456-426614174000');
        $resource = $this->createMock(EntityInterface::class);
        $resource->method('getId')->willReturn($identifier);
        $existing = $this->createMock(EntityInterface::class);
        $existing->method('getId')->willReturn($identifier);
        $apieFacade = $this->createMock(ApieFacadeInterface::class);
        $apieFacade->method('denormalizeNewObject')->willReturn($resource);
        $apieFacade->method('find')->willReturn($existing);
        $apieFacade->expects($this->never())->method('persistExisting');
        $dataLayer = $this->createMock(ApieDatalayer::class);
        $dataLayer->method('getMetadata')->willReturn(new DataLayerMetadata(
            new DateTimeImmutable('2025-01-01T00:00:00+00:00'),
            new DateTimeImmutable('2025-02-01T00:00:00+00:00'),
            '1.0.0',
            '1.0.1'
        ));
        $context = new ApieContext([
            ContextConstants::RESOURCE_NAME => UserWithAddress::class,
            ContextConstants::BOUNDED_CONTEXT_ID => 'default',
            ContextConstants::REST_API => true,
            RequestMethod::class => RequestMethod::PUT,
            ServerRequestInterface::class => new ServerRequest('PUT', '/users', [
                'If-Match' => '"stale-etag"',
            ]),
            ApieDatalayer::class => $dataLayer,
            LockFactory::class => new LockFactory(new NullStore()),
        ]);

        $response = (new CreateObjectAction($apieFacade))($context, []);

        $this->assertSame(409, $response->getStatusCode());
        $this->assertInstanceOf(EntityEtagConflictException::class, $response->error);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_does_not_replace_an_existing_object_the_user_cannot_access(): void
    {
        $identifier = UserWithAddressIdentifier::fromNative('123e4567-e89b-12d3-a456-426614174000');
        $resource = $this->createMockForIntersectionOfInterfaces([
            EntityInterface::class,
            RequiresPermissionsInterface::class,
        ]);
        $resource->method('getId')->willReturn($identifier);
        $existing = $this->createMockForIntersectionOfInterfaces([
            EntityInterface::class,
            RequiresPermissionsInterface::class,
        ]);
        $existing->method('getId')->willReturn($identifier);
        $existing->method('getRequiredPermissions')->willReturn(new PermissionList(['owner']));
        $apieFacade = $this->createMock(ApieFacadeInterface::class);
        $apieFacade->method('denormalizeNewObject')->willReturn($resource);
        $apieFacade->method('find')->willReturn($existing);
        $apieFacade->expects($this->never())->method('persistExisting');
        $apieFacade->expects($this->never())->method('persistNew');
        $context = new ApieContext([
            ContextConstants::RESOURCE_NAME => UserWithAddress::class,
            ContextConstants::BOUNDED_CONTEXT_ID => 'default',
            RequestMethod::class => RequestMethod::PUT,
            LockFactory::class => new LockFactory(new NullStore()),
        ]);

        $response = (new CreateObjectAction($apieFacade))($context, []);

        $this->assertSame(409, $response->getStatusCode());
    }
}
