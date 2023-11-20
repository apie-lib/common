<?php
namespace Apie\Common\ContextBuilders;

use Apie\Core\Context\ApieContext;
use Apie\Core\ContextBuilders\ContextBuilderInterface;
use ReflectionClass;
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
            $service = $this->serviceLocator->get($serviceId);
            $refl = new ReflectionClass($service);
            foreach ($refl->getInterfaceNames() as $interfaceName) {
                if (!$context->hasContext($interfaceName)) {
                    $context = $context->withContext($interfaceName, $service);
                }
            }
            $context = $context->withContext($serviceId, $service);
        }
        
        return $context;
    }
}
