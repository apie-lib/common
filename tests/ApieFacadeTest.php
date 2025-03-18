<?php
namespace Apie\Tests\Common;

use Apie\Common\Actions\CreateObjectAction;
use Apie\Common\Tests\Concerns\ProvidesApieFacade;
use Apie\Core\Context\ApieContext;
use PHPUnit\Framework\TestCase;

class ApieFacadeTest extends TestCase
{
    use ProvidesApieFacade;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_request_an_action_instance()
    {
        $testItem = $this->givenAnApieFacade(CreateObjectAction::class);
        $action = $testItem->getAction('default', 'test', new ApieContext());
        $this->assertInstanceOf(CreateObjectAction::class, $action);
    }
}
