<?php
namespace Apie\Common\Actions;

use Apie\Common\ApieFacade;
use Apie\Common\ApieFacadeAction;
use Apie\Common\ContextConstants;
use Apie\Core\Context\ApieContext;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Repositories\Search\QuerySearch;

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
    public function __invoke(ApieContext $context, array $rawContents): ItemHashmap
    {
        $resourceClass = $context->getContext(ContextConstants::RESOURCE_NAME);
        if (!is_a($resourceClass, EntityInterface::class, true)) {
            throw new InvalidTypeException($resourceClass, 'EntityInterface');
        }
        $result = $this->apieFacade->all($resourceClass)
            ->toPaginatedResult(QuerySearch::fromArray($rawContents));
        return $this->apieFacade->normalize($result, $context);
    }
}
