<?php
namespace Apie\Common\ContextBuilders;

use Apie\Core\Context\ApieContext;
use Apie\Core\ContextBuilders\ContextBuilderInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @TODO lazy initialization
 *
 * Adds services from Symfony in the ApieContext. Services with the tag 'apie.context' will be added
 */
final class ServiceContextBuilder implements ContextBuilderInterface
{
    /**
     * @param ServiceLocator<mixed> $serviceLocator
     */
    public function __construct(private readonly ServiceLocator $serviceLocator)
    {
    }
    
    public function process(ApieContext $context): ApieContext
    {
        foreach (array_keys($this->serviceLocator->getProvidedServices()) as $serviceId) {
            $context = $context->withContext($serviceId, $this->serviceLocator->get($serviceId));
        }
        return $context;
    }
}
