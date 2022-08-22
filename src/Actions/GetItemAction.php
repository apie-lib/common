<?php
namespace Apie\Common\Actions;

use Apie\Common\ApieFacade;
use Apie\Common\ApieFacadeAction;
use Apie\Common\ContextConstants;
use Apie\Core\Actions\ActionResponse;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\IdentifierUtils;
use ReflectionClass;

/**
 * Action to get a single item resource.
 */
final class GetItemAction implements ApieFacadeAction
{
    public function __construct(private readonly ApieFacade $apieFacade)
    {
    }
    /**
     * @param array<string|int, mixed> $rawContents
     */
    public function __invoke(ApieContext $context, array $rawContents): ActionResponse
    {
        $resourceClass = new ReflectionClass($context->getContext(ContextConstants::RESOURCE_NAME));
        $id = $context->getContext(ContextConstants::RESOURCE_ID);
        if (!$resourceClass->implementsInterface(EntityInterface::class)) {
            throw new InvalidTypeException($resourceClass->name, 'EntityInterface');
        }
        $result = $this->apieFacade->find(
            IdentifierUtils::entityClassToIdentifier($resourceClass)->newInstance($id),
            new BoundedContextId($context->getContext(ContextConstants::BOUNDED_CONTEXT_ID))
        );
        return ActionResponse::createRunSuccess($this->apieFacade, $context, $result, $result);
    }
}
