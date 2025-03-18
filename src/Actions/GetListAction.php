<?php
namespace Apie\Common\Actions;

use Apie\Core\Actions\ActionInterface;
use Apie\Core\Actions\ActionResponse;
use Apie\Core\Actions\ActionResponseStatus;
use Apie\Core\Actions\ActionResponseStatusList;
use Apie\Core\Actions\ApieFacadeInterface;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Core\Datalayers\Search\QuerySearch;
use Apie\Core\Dto\ListOf;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\Lists\StringList;
use LogicException;
use ReflectionClass;

/**
 * Action to get a list of resources.
 */
final class GetListAction implements ActionInterface
{
    public function __construct(private readonly ApieFacadeInterface $apieFacade)
    {
    }

    public static function isAuthorized(ApieContext $context, bool $runtimeChecks, bool $throwError = false): bool
    {
        $refl = new ReflectionClass($context->getContext(ContextConstants::RESOURCE_NAME, $throwError));
        return $context->appliesToContext($refl, $runtimeChecks, $throwError ? new LogicException("Class can not be accessed") : null);
    }

    /**
     * @param array<string|int, mixed> $rawContents
     */
    public function __invoke(ApieContext $context, array $rawContents): ActionResponse
    {
        $context->withContext(ContextConstants::APIE_ACTION, __CLASS__)->checkAuthorization();
        $resourceClass = $context->getContext(ContextConstants::RESOURCE_NAME);
        if (!is_a($resourceClass, EntityInterface::class, true)) {
            throw new InvalidTypeException($resourceClass, 'EntityInterface');
        }
        $resource =  $this->apieFacade->all(
            $resourceClass,
            new BoundedContextId($context->getContext(ContextConstants::BOUNDED_CONTEXT_ID))
        );
        $result = $resource->toPaginatedResult(QuerySearch::fromArray($rawContents, $context));
        return ActionResponse::createRunSuccess($this->apieFacade, $context, $result, $resource);
    }

    public static function getInputType(ReflectionClass $class): ReflectionClass
    {
        return $class;
    }

    public static function getOutputType(ReflectionClass $class): ListOf
    {
        return new ListOf($class);
    }

    public static function getPossibleActionResponseStatuses(): ActionResponseStatusList
    {
        return new ActionResponseStatusList([
            ActionResponseStatus::SUCCESS
        ]);
    }

    public static function getDescription(ReflectionClass $class): string
    {
        return 'Gets a list of resource that are an instance of ' . $class->getShortName();
    }
    
    public static function getTags(ReflectionClass $class): StringList
    {
        return new StringList([$class->getShortName(), 'resource']);
    }

    public static function getRouteAttributes(ReflectionClass $class): array
    {
        return [
            ContextConstants::GET_ALL_OBJECTS => true,
            ContextConstants::GET_OBJECT => true,
            ContextConstants::RESOURCE_NAME => $class->name,
        ];
    }
}
