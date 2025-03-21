<?php
namespace Apie\Tests\Common\Actions;

use Apie\Common\Actions\RunAction;
use Apie\Common\Tests\Concerns\ProvidesApieFacade;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Fixtures\Actions\StaticActionExample;
use Apie\Serializer\Lists\SerializedList;
use PHPUnit\Framework\TestCase;

class RunActionTest extends TestCase
{
    use ProvidesApieFacade;
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_run_a_method()
    {
        $testItem = $this->givenAnApieFacade(RunAction::class);
        $context = new ApieContext([
            ContextConstants::SERVICE_CLASS => StaticActionExample::class,
            ContextConstants::METHOD_NAME => 'secretCode',
            ContextConstants::BOUNDED_CONTEXT_ID => 'default',
        ]);
        /** @var RunAction $action */
        $action = $testItem->getAction('default', 'test', $context);
        $response = $action(
            $context,
            [
            ]
        );

        $this->assertInstanceOf(SerializedList::class, $response->getResultAsNativeData());
        $this->assertEquals(
            StaticActionExample::secretCode()->toArray(),
            $response->getResultAsNativeData()->toArray()
        );
    }
}
