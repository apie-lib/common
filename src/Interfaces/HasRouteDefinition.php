<?php
namespace Apie\Common\Interfaces;

use Apie\Core\Enums\RequestMethod;
use Apie\Core\ValueObjects\UrlRouteDefinition;

/**
 * Route definition interface. This is used internally used by Apie so it can be converted to whatever route library
 * Apie is used in.
 */
interface HasRouteDefinition
{
    public function getMethod(): RequestMethod;
    public function getUrl(): UrlRouteDefinition;
    /**
     * @return class-string<object>
     */
    public function getController(): string;
    /**
     * @return array<string, mixed>
     */
    public function getRouteAttributes(): array;
    public function getOperationId(): string;
}
