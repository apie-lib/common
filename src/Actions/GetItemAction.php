<?php
namespace Apie\Common\Actions;

use Apie\Common\ApieFacade;
use Apie\Common\ApieFacadeAction;
use Apie\Common\ContextConstants;
use Apie\Common\Utils;
use Apie\Core\Context\ApieContext;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\Lists\ItemHashmap;

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
    public function __invoke(ApieContext $context, array $rawContents): ItemHashmap
    {
        $resourceClass = $context->getContext(ContextConstants::RESOURCE_NAME);
        $id = $context->getContext(ContextConstants::RESOURCE_ID);
        if (!is_a($resourceClass, EntityInterface::class, true)) {
            throw new InvalidTypeException($resourceClass, 'EntityInterface');
        }
        $result = $this->apieFacade->find(Utils::entityClassToIdentifier($resourceClass)->newInstance($id));
        return $this->apieFacade->normalize($result, $context);
    }
}
