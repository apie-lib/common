<?php
namespace Apie\Common\RouteDefinitions;

use Apie\Common\Interfaces\RestApiRouteDefinition;
use Apie\Core\Actions\ActionResponseStatusList;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\ContextConstants;
use Apie\Core\Dto\ListOf;
use Apie\Core\Lists\StringList;
use ReflectionClass;
use ReflectionMethod;
use ReflectionType;

abstract class AbstractRestApiRouteDefinition implements RestApiRouteDefinition
{
    /**
     * @param ReflectionClass<covariant object> $class
     */
    public function __construct(
        protected readonly ReflectionClass $class,
        protected readonly ?BoundedContextId $boundedContextId = null,
        protected readonly ?ReflectionMethod $method = null
    ) {
    }

    /**
     * @return ReflectionClass<covariant object>|ReflectionMethod|ReflectionType
     */
    final public function getInputType(): ReflectionClass|ReflectionMethod|ReflectionType
    {
        $actionClass = $this->getAction();
        return $actionClass::getInputType($this->class, $this->method);
    }

    /**
     * @return ReflectionClass<covariant object>|ReflectionMethod|ReflectionType|ListOf
     */
    final public function getOutputType(): ReflectionClass|ReflectionMethod|ReflectionType|ListOf
    {
        $actionClass = $this->getAction();
        return $actionClass::getOutputType($this->class, $this->method);
    }

    final public function getPossibleActionResponseStatuses(): ActionResponseStatusList
    {
        $actionClass = $this->getAction();
        return $actionClass::getPossibleActionResponseStatuses($this->method);
    }

    /**
     * @return class-string<covariant object>
     */
    abstract public function getController(): string;

    final public function getDescription(): string
    {
        $actionClass = $this->getAction();
        return $actionClass::getDescription($this->class, $this->method);
    }

    final public function getTags(): StringList
    {
        $actionClass = $this->getAction();
        return $actionClass::getTags($this->class, $this->method);
    }

    final public function getRouteAttributes(): array
    {
        $actionClass = $this->getAction();
        $attributes = $actionClass::getRouteAttributes($this->class, $this->method);
        $attributes[ContextConstants::APIE_ACTION] = $this->getAction();
        $attributes[ContextConstants::OPERATION_ID] = $this->getOperationId();
        $attributes[ContextConstants::BOUNDED_CONTEXT_ID] = $this->boundedContextId->toNative();
        return $attributes;
    }
}
