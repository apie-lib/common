<?php
namespace Apie\Common\Actions;

use Apie\Common\ApieFacade;
use Apie\Common\ApieFacadeAction;
use Apie\Common\ContextConstants;
use Apie\Core\Actions\ActionResponse;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\Datalayers\Search\QuerySearch;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Exceptions\InvalidTypeException;

/**
 * Action to get a list of resources.
 */
final class GetListAction implements ApieFacadeAction
{
    public function __construct(private readonly ApieFacade $apieFacade)
    {
    }

    /**
     * @param array<string|int, mixed> $rawContents
     */
    public function __invoke(ApieContext $context, array $rawContents): ActionResponse
    {
        $resourceClass = $context->getContext(ContextConstants::RESOURCE_NAME);
        if (!is_a($resourceClass, EntityInterface::class, true)) {
            throw new InvalidTypeException($resourceClass, 'EntityInterface');
        }
        $resource =  $this->apieFacade->all(
            $resourceClass,
            new BoundedContextId($context->getContext(ContextConstants::BOUNDED_CONTEXT_ID))
        );
        $result = $resource->toPaginatedResult(QuerySearch::fromArray($rawContents));
        return ActionResponse::createRunSuccess($this->apieFacade, $context, $result, $resource);
    }
}
