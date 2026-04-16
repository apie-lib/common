<?php
namespace Apie\Common\Command;

use Apie\Common\Other\Audit\AuditMigration;
use Apie\Common\Other\AuditLog;
use Apie\Core\Attributes\Auditable;
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
use Apie\Core\ValueObjects\IdFriendlyEntityReference;
use Apie\Core\ValueObjects\NonEmptyString;
use Apie\Core\ValueObjects\Utils;
use Apie\Serializer\ValueObjects\SerializedPhpObject;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ApieAuditLogForMigrationCommand extends Command
{
    private const CHUNKSIZE = 2000;
    public function __construct(
        private readonly BoundedContextHashmap $boundedContextHashmap,
        private readonly ApieDatalayer $apieDatalayer,
        private readonly ContextBuilderFactory $contextBuilderFactory
    ) {
        parent::__construct('apie:audit-log-for-migration');
    }
    protected function configure(): void
    {
        $this->setDescription('This command will create audit log entries for all resources that are being updated by a database migration.');
        $this->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'limit number of resources to check');
        $this->addOption('resource', 'r', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'only check this resource');
        $this->addOption('bounded-context', 'b', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'only check this bounded context');
    }

    private function skipBoundedContext(InputInterface $input, string $contextId): bool
    {
        $boundedContexts = $input->getOption('bounded-context');
        if (empty($boundedContexts)) {
            return false;
        }
        return !in_array($contextId, $boundedContexts);
    }

    /**
     * @param ReflectionClass<EntityInterface> $resource
     */
    private function skipResource(InputInterface $input, ReflectionClass $resource): bool
    {
        $resources = $input->getOption('resource');
        $attributes = $resource->getAttributes(Auditable::class);
        if (empty($attributes)) {
            return true;
        }
        if (empty($resources)) {
            return false;
        }
        return !in_array($resource->getShortName(), $resources);
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
            Command::class => $this,
            ContextConstants::DISABLE_CONTEXT_FILTER => true,
        ]);
        /** @var BoundedContext $boundedContext */
        foreach ($this->boundedContextHashmap as $contextId => $boundedContext) {
            if ($this->skipBoundedContext($input, $contextId)) {
                continue;
            }
            $subApieContext = $apieContext->registerInstance($boundedContext)
                ->withContext(ContextConstants::BOUNDED_CONTEXT_ID, $contextId)
                ->registerInstance(new BoundedContextId($contextId));
            /** @var ReflectionClass<EntityInterface> $resource */
            foreach ($boundedContext->resources as $resource) {
                if ($this->skipResource($input, $resource)) {
                    continue;
                }
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
                        $output->write(sprintf('%40s', Utils::toString($item->getId())));
                        $stop = false;
                        /** @var EntityInterface $item */
                        $createdResource = $this->apieDatalayer->persistExisting($item, $boundedContextId);
                        $this->apieDatalayer->persistNew(
                            new AuditLog(
                                new IdFriendlyEntityReference(
                                    $boundedContextId,
                                    NonEmptyString::fromNative($resource->getShortName()),
                                    NonEmptyString::fromNative($createdResource->getId())),
                                    SerializedPhpObject::createFromPhpObject($createdResource),
                                    new AuditMigration(),
                                    null
                                ),
                                $boundedContextId
                            );
                        $output->writeln(' Done');
                    }
                } while (!$stop || $limit !== null);
            }
        }
        return Command::SUCCESS;
    }
}
