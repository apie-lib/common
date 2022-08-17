<?php
namespace Apie\Common\Actions;

use Apie\Common\ApieFacade;
use Apie\Common\ApieFacadeAction;
use Apie\Common\ContextConstants;
use Apie\Core\Context\ApieContext;
use ReflectionMethod;

/**
 * Runs a global method and returns the return value of this method.
 */
final class RunAction implements ApieFacadeAction
{
    public function __construct(private ApieFacade $apieFacade)
    {
    }

    /**
     * @param array<string|int, mixed> $rawContents
     */
    public function __invoke(ApieContext $context, array $rawContents): mixed
    {
        $method = new ReflectionMethod(
            $context->getContext(ContextConstants::SERVICE_CLASS),
            $context->getContext(ContextConstants::METHOD_NAME)
        );
        $object = $method->isStatic()
            ? null
            : $context->getContext($context->getContext(ContextConstants::SERVICE_CLASS));
        $returnValue = $this->apieFacade->denormalizeOnMethodCall($rawContents, $object, $method, $context);
        return $this->apieFacade->normalize($returnValue, $context);
    }
}
