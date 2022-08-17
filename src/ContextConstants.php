<?php
namespace Apie\Common;

use Apie\Common\Actions\RunAction;
use Apie\Core\Actions\HasRouteDefinition;
use Apie\Core\ContextBuilders\ContextBuilderInterface;

/**
 * Contains a list of context key constants as used for ApieContext and in route definitions (which are copied to
 * ApieContext values).
 */
final class ContextConstants
{
    private function __construct()
    {
    }

    /**
     * ID of the selected bounded context.
     */
    public const BOUNDED_CONTEXT_ID = 'boundedContextId';
    /**
     * Name of resource entity. Used by getting and creating resources of a specific class.
     */
    public const RESOURCE_NAME = 'resourceName';
    /**
     * Internal operation id of action used.
     *
     * @see HasRouteDefinition::getOperationId()
     */
    public const OPERATION_ID = 'operationId';

    /**
     * Used for running a specific method call.
     * @see RunAction
     */
    public const SERVICE_CLASS = 'serviceClass';

    /**
     * Used for running a specific method call.
     * @see RunAction
     */
    public const METHOD_NAME = 'methodName';

    /**
     * Raw contents of POST body or GET parameters.
     */
    public const RAW_CONTENTS = 'raw-contents';
}
