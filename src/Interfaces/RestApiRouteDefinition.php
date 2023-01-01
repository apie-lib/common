<?php
namespace Apie\Common\Interfaces;

use Apie\Common\Interfaces\HasActionDefinition;
use Apie\Common\Interfaces\HasRouteDefinition;
use Apie\Common\Lists\UrlPrefixList;
use Apie\Core\Actions\ActionResponseStatusList;
use Apie\Core\Dto\ListOf;
use Apie\Core\Lists\StringList;
use ReflectionClass;
use ReflectionMethod;
use ReflectionType;

interface RestApiRouteDefinition extends HasRouteDefinition, HasActionDefinition
{
    /**
     * @return ReflectionClass<object>|ReflectionMethod|ReflectionType
     */
    public function getInputType(): ReflectionClass|ReflectionMethod|ReflectionType;

    /**
     * @return ReflectionClass<object>|ReflectionMethod|ReflectionType|ListOf
     */
    public function getOutputType(): ReflectionClass|ReflectionMethod|ReflectionType|ListOf;

    public function getPossibleActionResponseStatuses(): ActionResponseStatusList;

    /**
     * @return class-string<object>
     */
    public function getController(): string;

    public function getDescription(): string;
    public function getTags(): StringList;
}
