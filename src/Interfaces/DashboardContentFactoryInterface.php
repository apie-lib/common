<?php
namespace Apie\Common\Interfaces;

use Stringable;

interface DashboardContentFactoryInterface
{
    /**
     * @param array<string, mixed> $templateParameters
     */
    public function create(
        string $template,
        array $templateParameters = []
    ): Stringable|string;
}
