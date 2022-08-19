<?php
namespace Apie\Common\Actions;

use Apie\Common\ApieFacade;
use Apie\Common\ApieFacadeAction;
use Apie\Common\ContextConstants;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;

/**
 * Action to create a new object.
 */
final class CreateObjectAction implements ApieFacadeAction
{
    public function __construct(private readonly ApieFacade $apieFacade)
    {
    }
    
    /**
     * @param array<string|int, mixed> $rawContents
     */
    public function __invoke(ApieContext $context, array $rawContents): mixed
    {
        $resource = $this->apieFacade->denormalizeNewObject(
            $rawContents,
            $context->getContext(ContextConstants::RESOURCE_NAME),
            $context
        );
        $resource = $this->apieFacade->persistNew($resource, new BoundedContextId($context->getContext(ContextConstants::BOUNDED_CONTEXT_ID)));
        return $this->apieFacade->normalize($resource, $context);
    }
}
