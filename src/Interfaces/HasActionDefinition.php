<?php
namespace Apie\Common\Interfaces;

use Apie\Core\Actions\ActionInterface;

/**
 * Interface for route definitions to tell they are linked to an Apie action.
 */
interface HasActionDefinition
{
    /**
     * @return class-string<ActionInterface>
     */
    public function getAction(): string;
}
