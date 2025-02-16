<?php
namespace Apie\Common\Command;

use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\ContextBuilders\ContextBuilderFactory;
use Apie\Core\ContextConstants;
use Apie\Core\Datalayers\ApieDatalayer;
use Apie\Core\Datalayers\Search\QuerySearch;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Entities\RequiresRecalculatingInterface;
use Apie\Core\Enums\ConsoleCommand;
use Apie\Core\Lists\StringHashmap;
use Apie\Core\ValueObjects\Utils;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ApieUpdateRecalculatingCommand extends Command
{
    private const CHUNKSIZE = 2000;
    public function __construct(
        private readonly BoundedContextHashmap $boundedContextHashmap,
        private readonly ApieDatalayer $apieDatalayer,
        private readonly ContextBuilderFactory $contextBuilderFactory,
    ) {
        parent::__construct('apie:recalculate-resources');
    }
    protected function configure(): void
    {
        $this->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'limit number of resources to check');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = $input->getOption('limit');
        if ($limit !== null) {
            $limit = (int) $limit;
        }
        $apieContext = $this->contextBuilderFactory->createGeneralContext([
            ConsoleCommand::class => ConsoleCommand::CONSOLE_COMMAND,
            ConsoleCommand::CONSOLE_COMMAND->value => true,
            ContextConstants::DISABLE_CONTEXT_FILTER => true,
        ]);
        /** @var BoundedContext $boundedContext */
        foreach ($this->boundedContextHashmap as $contextId => $boundedContext) {
            $subApieContext = $apieContext->registerInstance($boundedContext)
                ->withContext(ContextConstants::BOUNDED_CONTEXT_ID, $contextId)
                ->registerInstance(new BoundedContextId($contextId));
            /** @var ReflectionClass<EntityInterface> $resource */
            foreach ($boundedContext->resources as $resource) {
                if (in_array(RequiresRecalculatingInterface::class, $resource->getInterfaceNames())) {
                    $offset = 0;
                    $boundedContextId = new BoundedContextId($contextId);
                    $output->writeln($resource->getShortName() . ' (' . $boundedContextId . ')');
                    $list = $this->apieDatalayer->all($resource, $boundedContextId);
                    do {
                        $chunk = $list->toPaginatedResult(
                            new QuerySearch(
                                $offset,
                                $limit ?? self::CHUNKSIZE,
                                textSearch: null,
                                searches: null,
                                orderBy: new StringHashmap(['timestamp' => 'ASC']),
                                apieContext: $subApieContext
                            )
                        );
                        $offset++;
                        $stop = true;
                        foreach ($chunk as $item) {
                            /** @var RequiresRecalculatingInterface $item */
                            $output->write(sprintf('%40s', Utils::toString($item->getId())));
                            $stop = false;
                            $this->apieDatalayer->persistExisting($item, $boundedContextId);
                            $output->writeln(' Done');
                        }
                    } while ($stop || $limit !== null);
                }
            }
        }
        return Command::SUCCESS;
    }
}
