<?php
namespace Apie\Common\Wrappers;

use Apie\Console\ConsoleCommandFactory as ConsoleConsoleCommandFactory;
use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\ContextBuilders\ContextBuilderFactory;
use Generator;
use Symfony\Component\Console\Application;

final class ConsoleCommandFactory
{
    public function __construct(
        private readonly ConsoleConsoleCommandFactory $factory,
        private readonly ContextBuilderFactory $contextBuilderFactory,
        private readonly BoundedContextHashmap $boundedContextHashmap
    ) {
    }

    public function create(Application $application): Generator
    {
        foreach ($this->boundedContextHashmap as $boundedContext) {
            $context = $this->contextBuilderFactory->createGeneralContext([
                Application::class => $application,
                BoundedContext::class => $boundedContext
            ]);
            yield from $this->factory->createForBoundedContext($boundedContext, $context);
        }
    }
}
