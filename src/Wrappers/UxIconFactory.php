<?php
namespace Apie\Common\Wrappers;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\UX\Icons\IconFactory;
use Symfony\UX\Icons\Iconify;
use Symfony\UX\Icons\IconRegistryInterface;
use Symfony\UX\Icons\IconRenderer;
use Symfony\UX\Icons\IconRendererInterface;
use Symfony\UX\Icons\Registry\CacheIconRegistry;
use Symfony\UX\Icons\Registry\IconifyOnDemandRegistry;
use Symfony\UX\Icons\Twig\UXIconRuntime;

class UxIconFactory
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ?CacheInterface $cache = null
    ) {
    }

    public function create(
        ?UXIconRuntime $runtimeLoader,
        IconRendererInterface $iconRenderer,
    ): UXIconRuntime {
        return $runtimeLoader ?? new UXIconRuntime(
            $iconRenderer,
            true,
            $this->logger
        );
    }

    public function createIconRenderer(
        ?IconRendererInterface $iconRenderer,
        IconRegistryInterface $registry
    ): IconRendererInterface {
        return $iconRenderer ?? new IconRenderer(
            $registry
        );
    }

    public function createIconRegistry(
        ?IconRegistryInterface $registry = null,
    ): IconRegistryInterface {
        if (!$registry) {
            $iconify = class_exists(IconFactory::class)
                ? new Iconify(
                    $this->cache ?? new \Symfony\Component\Cache\Adapter\ArrayAdapter(),
                    new IconFactory()
                ) : new Iconify(
                    $this->cache ?? new \Symfony\Component\Cache\Adapter\ArrayAdapter(),
                );
            $registry = new IconifyOnDemandRegistry($iconify);
            if ($this->cache) {
                $registry = new CacheIconRegistry(
                    $registry,
                    $this->cache
                );
            }
        }
        return $registry;
    }
}
