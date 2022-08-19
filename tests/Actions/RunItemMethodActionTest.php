<?php
namespace Apie\Tests\Common\Actions;

use Apie\Common\Actions\RunItemMethodAction;
use Apie\Common\ContextConstants;
use Apie\Common\Tests\Concerns\ProvidesApieFacade;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Fixtures\Entities\UserWithAddress;
use Apie\Fixtures\ValueObjects\AddressWithZipcodeCheck;
use Apie\Fixtures\ValueObjects\Password;
use Apie\TextValueObjects\DatabaseText;
use PHPUnit\Framework\TestCase;

class RunItemMethodActionTest extends TestCase
{
    use ProvidesApieFacade;
    /** @test */
    public function it_can_run_a_method()
    {
        $testItem = $this->givenAnApieFacade(RunItemMethodAction::class);
        $entity = new UserWithAddress(new AddressWithZipcodeCheck(
            new DatabaseText('street'),
            new DatabaseText('42'),
            new DatabaseText('1111'),
            new DatabaseText('Washington DC')
        ));
        $entity->setPassword(new Password('Strong-Password32'));
        $testItem->persistNew($entity, new BoundedContextId('default'));

        $context = new ApieContext([
            ContextConstants::RESOURCE_NAME => UserWithAddress::class,
            ContextConstants::RESOURCE_ID => $entity->getId()->toNative(),
            ContextConstants::METHOD_CLASS => UserWithAddress::class,
            ContextConstants::METHOD_NAME => 'verifyAuthentication',
            ContextConstants::BOUNDED_CONTEXT_ID => 'default',
        ]);
        /** @var RunItemMethodAction $action */
        $action = $testItem->getAction('default', 'test', $context);
        $response = $action(
            $context,
            [
                'username' => $entity->getId()->toNative(),
                'password' => 'Strong-Password32',
            ]
        );

        $this->assertTrue($response);
    }
}
