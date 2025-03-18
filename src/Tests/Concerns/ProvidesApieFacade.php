<?php
namespace Apie\Common\Tests\Concerns;

use Apie\Common\ApieFacade;
use Apie\Common\Enums\UrlPrefix;
use Apie\Common\Interfaces\ApieFacadeInterface;
use Apie\Common\Interfaces\HasActionDefinition;
use Apie\Common\Interfaces\HasRouteDefinition;
use Apie\Common\Interfaces\RouteDefinitionProviderInterface;
use Apie\Common\Lists\UrlPrefixList;
use Apie\Common\RouteDefinitions\ActionHashmap;
use Apie\Core\Actions\ActionInterface;
use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\Enums\RequestMethod;
use Apie\Core\ValueObjects\UrlRouteDefinition;
use Apie\Fixtures\BoundedContextFactory;
use Apie\Fixtures\TestHelpers\TestWithInMemoryDatalayer;
use Apie\Serializer\Serializer;
use LogicException;

/**
 * Creates an Apie Facade for testing actions.
 *
 * In tests you would write it often like this:
 * ```php
 * $apieFacade = $this->givenAnApieFacade(ActionClass::class);
 * $context = new ApieContext();
 * $action = $apieFacade->getAction('default', 'test', $context);
 * ```
 *
 * @codeCoverageIgnore
 */
trait ProvidesApieFacade
{
    use TestWithInMemoryDatalayer;

    /** @param class-string<ActionInterface> $apieFacadeActionClass */
    public function givenAnApieFacade(string $apieFacadeActionClass, ?BoundedContextHashmap $boundedContextHashmap = null): ApieFacadeInterface
    {
        $routeDefinitionProvider = new class($apieFacadeActionClass) implements RouteDefinitionProviderInterface {
            /** @param class-string<ActionInterface> $apieFacadeActionClass */
            public function __construct(private readonly string $apieFacadeActionClass)
            {
            }

            public function getActionsForBoundedContext(BoundedContext $boundedContext, ApieContext $apieContext): ActionHashmap
            {
                $routeDefinition =  new class($this->apieFacadeActionClass) implements HasRouteDefinition, HasActionDefinition {
                    /** @param class-string<ActionInterface> $apieFacadeActionClass */
                    public function __construct(private readonly string $apieFacadeActionClass)
                    {
                    }

                    public function getMethod(): RequestMethod
                    {
                        return RequestMethod::GET;
                    }
                    
                    public function getUrl(): UrlRouteDefinition
                    {
                        return new UrlRouteDefinition('/test');
                    }
                
                    public function getController(): string
                    {
                        throw new LogicException('not implemented');
                    }
                    public function getRouteAttributes(): array
                    {
                        return [];
                    }

                    public function getAction(): string
                    {
                        return $this->apieFacadeActionClass;
                    }

                    public function getOperationId(): string
                    {
                        return 'test';
                    }

                    public function getUrlPrefixes(): UrlPrefixList
                    {
                        return new UrlPrefixList([UrlPrefix::CMS]);
                    }
                };
                return new ActionHashmap(
                    [
                        'test' => $routeDefinition
                    ]
                );
            }
        };

        return new ApieFacade(
            $routeDefinitionProvider,
            $boundedContextHashmap ?? BoundedContextFactory::createHashmap(),
            Serializer::create(),
            $this->givenAnInMemoryDataLayer(new BoundedContextId('default'))
        );
    }
}
