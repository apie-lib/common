<?php
namespace Apie\Tests\Common\Actions;

use Apie\Common\Actions\RunAction;
use Apie\Common\ContextConstants;
use Apie\Common\Tests\Concerns\ProvidesApieFacade;
use Apie\Core\Context\ApieContext;
use Apie\Fixtures\Actions\StaticActionExample;
use Apie\Serializer\Lists\SerializedList;
use PHPUnit\Framework\TestCase;

class RunActionTest extends TestCase
{
    use ProvidesApieFacade;
    /** @test */
    public function it_can_run_a_method()
    {
        $testItem = $this->givenAnApieFacade(RunAction::class);
        $context = new ApieContext([
            ContextConstants::SERVICE_CLASS => StaticActionExample::class,
            ContextConstants::METHOD_NAME => 'secretCode',
        ]);
        /** @var RunAction $action */
        $action = $testItem->getAction('default', 'test', $context);
        $response = $action(
            $context,
            [
            ]
        );

        $this->assertInstanceOf(SerializedList::class, $response);
        $this->assertEquals(
            StaticActionExample::secretCode()->toArray(),
            $response->toArray()
        );
    }
}
