<?php
namespace Apie\Common;

use Apie\Common\Actions\RunAction;
use Apie\Common\Actions\RunItemMethodAction;
use Apie\Core\Actions\HasRouteDefinition;

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
     * Id of resource. This one is often filled in the routing placeholder.
     */
    public const RESOURCE_ID = 'id';

    /**
     * Internal operation id of action used.
     *
     * @see HasRouteDefinition::getOperationId()
     */
    public const OPERATION_ID = 'operationId';

    /**
     * Internal class used for the Apie action.
     */
    public const APIE_ACTION = '_apie_action';

    /**
     * Used for running a specific method call.
     * @see RunAction
     */
    public const SERVICE_CLASS = 'serviceClass';

    /**
     * Used for picking the right class of a method.
     *
     * @see RunItemMethodAction
     */
    public const METHOD_CLASS = 'methodClass';

    /**
     * Used for running a specific method call.
     * @see RunAction
     * @see RunItemMethodAction
     */
    public const METHOD_NAME = 'methodName';

    /**
     * Raw contents of POST body or GET parameters.
     */
    public const RAW_CONTENTS = 'raw-contents';

    /**
     * Added if an OpenAPI spec is generated.
     */
    public const OPENAPI = 'openapi';

    /**
     * Added if a REST API call is done.
     */
    public const REST_API='rest';

    /**
     * Added if CMS request is done.
     */
    public const CMS='cms';

    public const CREATE_OBJECT = 'create';

    public const EDIT_OBJECT = 'edit';

    public const REMOVE_OBJECT = 'remove';

    public const GET_ALL_OBJECTS = 'all';

    public const GET_OBJECT = 'get';

    public const RESOURCE_METHOD = 'resource-call';

    public const GLOBAL_METHOD = 'method-call';
}
