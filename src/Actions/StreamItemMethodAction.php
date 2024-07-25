<?php
namespace Apie\Common\Actions;

use Apie\Common\IntegrationTestLogger;
use Apie\Core\Actions\ActionResponse;
use Apie\Core\Actions\ActionResponseStatus;
use Apie\Core\Actions\ActionResponseStatusList;
use Apie\Core\Actions\ApieFacadeInterface;
use Apie\Core\Actions\MethodActionInterface;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Exceptions\EntityNotFoundException;
use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\FileStorage\StoredFile;
use Apie\Core\IdentifierUtils;
use Apie\Core\Lists\StringList;
use Apie\Core\TypeUtils;
use Apie\Core\Utils\EntityUtils;
use Apie\Core\ValueObjects\Exceptions\InvalidStringForValueObjectException;
use Apie\Serializer\Exceptions\ValidationException;
use Exception;
use LogicException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Runs a method from  a resource and will stream the result.
 */
final class StreamItemMethodAction implements MethodActionInterface
{
    public function __construct(private readonly ApieFacadeInterface $apieFacade)
    {
    }

    public static function isAuthorized(ApieContext $context, bool $runtimeChecks, bool $throwError = false): bool
    {
        $refl = new ReflectionClass($context->getContext(ContextConstants::RESOURCE_NAME, $throwError));
        $methodName = $context->getContext(ContextConstants::METHOD_NAME, $throwError);
        $method = new ReflectionMethod(
            $context->getContext(ContextConstants::METHOD_CLASS, $throwError),
            $methodName
        );
        if (EntityUtils::isPolymorphicEntity($refl) && $runtimeChecks && $context->hasContext(ContextConstants::RESOURCE) &&!$method->isStatic()) {
            $refl = new ReflectionClass($context->getContext(ContextConstants::RESOURCE, $throwError));
            if (!$refl->hasMethod($methodName)) {
                if ($throwError) {
                    throw new LogicException('Method ' . $methodName . ' does not exist on this entity');
                }
                return false;
            }
            $method = $refl->getMethod($methodName);
        }
        if (!$context->appliesToContext($refl, $runtimeChecks, $throwError ? new LogicException('Class access is not allowed!') : null)) {
            return false;
        }
        return $context->appliesToContext($method, $runtimeChecks, $throwError ? new LogicException('Class method is not allowed') : null);
    }

    /**
     * @param array<string|int, mixed> $rawContents
     */
    public function __invoke(ApieContext $context, array $rawContents): ActionResponse
    {
        $context->withContext(ContextConstants::APIE_ACTION, __CLASS__)->checkAuthorization();
        $resourceClass = new ReflectionClass($context->getContext(ContextConstants::RESOURCE_NAME));
        if (!$resourceClass->implementsInterface(EntityInterface::class)) {
            throw new InvalidTypeException($resourceClass->name, 'EntityInterface');
        }
        $method = new ReflectionMethod(
            $context->getContext(ContextConstants::METHOD_CLASS),
            $context->getContext(ContextConstants::METHOD_NAME)
        );
        if ($method->isStatic()) {
            $resource = null;
        } else {
            $id = $context->getContext(ContextConstants::RESOURCE_ID);
            try {
                $resource = $this->apieFacade->find(
                    IdentifierUtils::entityClassToIdentifier($resourceClass)->newInstance($id),
                    new BoundedContextId($context->getContext(ContextConstants::BOUNDED_CONTEXT_ID))
                );
            } catch (InvalidStringForValueObjectException|EntityNotFoundException $error) {
                IntegrationTestLogger::logException($error);
                return ActionResponse::createClientError($this->apieFacade, $context, $error);
            }
            $context = $context->withContext(ContextConstants::RESOURCE, $resource);
            // polymorphic relation, so could be the incorrect declared method
            if (!$method->getDeclaringClass()->isInstance($resource)) {
                try {
                    $method = (new ReflectionClass($resource))->getMethod($method->name);
                } catch (ReflectionException $methodError) {
                    $error = new Exception(
                        sprintf('Resource "%s" does not support "%s"!', $id, $method->name),
                        0,
                        $methodError
                    );
                    throw ValidationException::createFromArray(['' => $error]);
                }
            }
        }

        $result = $this->apieFacade->denormalizeOnMethodCall(
            $rawContents,
            $resource,
            $method,
            $context
        );
        $result = $this->toDownload($result);

        return ActionResponse::createRunSuccess($this->apieFacade, $context, $result, $resource);
    }

    private function toDownload(mixed $result): ResponseInterface
    {
        $factory = new Psr17Factory();
        $response = $factory->createResponse(200);
        if (is_resource($result)) {
            $stream = Stream::create($result);
            $response = $response->withBody($stream);
            $response = $response->withHeader('Content-Type', 'application/octet-stream');
            return $response;
        }
        if ($result instanceof UploadedFileInterface) {
            $stream = $result->getStream();
            $response = $response->withBody($stream);
            $filename = $result->getClientFilename();
            $mimeType = $result instanceof StoredFile ? $result->getServerMimeType() : $result->getClientMediaType();
            $response = $response->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
            $response = $response->withHeader('Content-Type', $mimeType);
            return $response;
        }
        throw ValidationException::createFromArray(['' => new LogicException('There is nothing to stream')]);
    }

    /**
     * Returns a string how we should display the method. For example we remove 'add' or 'remove' from the string.
     */
    public static function getDisplayNameForMethod(ReflectionMethod $method): string
    {
        if ($method->getNumberOfParameters() > 0) {
            if (str_starts_with($method->name, 'remove')) {
                return lcfirst(substr($method->name, strlen('remove')));
            }
            if (str_starts_with($method->name, 'add')) {
                return lcfirst(substr($method->name, strlen('add')));
            }
        }
        if (str_starts_with($method->name, 'get') && TypeUtils::couldBeAStream($method->getReturnType())) {
            return lcfirst(substr($method->name, strlen('get')));
        }
        return $method->name;
    }

    /** @param ReflectionClass<object> $class */
    public static function getInputType(ReflectionClass $class, ?ReflectionMethod $method = null): ReflectionMethod
    {
        assert($method instanceof ReflectionMethod);
        return $method;
    }

    /** @param ReflectionClass<object> $class */
    public static function getOutputType(ReflectionClass $class, ?ReflectionMethod $method = null): ReflectionMethod|ReflectionClass
    {
        assert($method instanceof ReflectionMethod);
        return $method;
    }

    public static function getPossibleActionResponseStatuses(?ReflectionMethod $method = null): ActionResponseStatusList
    {
        assert($method instanceof ReflectionMethod);
        $list = [ActionResponseStatus::SUCCESS];

        if (!empty($method->getParameters())) {
            $list[] = ActionResponseStatus::CLIENT_ERROR;
        }
        if (!$method->isStatic()) {
            $list[] = ActionResponseStatus::NOT_FOUND;
        }
        return new ActionResponseStatusList($list);
    }

    /**
     * @param ReflectionClass<object> $class
     */
    public static function getDescription(ReflectionClass $class, ?ReflectionMethod $method = null): string
    {
        assert($method instanceof ReflectionMethod);
        $name = self::getDisplayNameForMethod($method);
        return 'Streams ' . $name . ' on a ' . $class->getShortName() . ' with a specific id';
    }
    
    /**
     * @param ReflectionClass<object> $class
     */
    public static function getTags(ReflectionClass $class, ?ReflectionMethod $method = null): StringList
    {
        $className = $class->getShortName();
        $declared = $method ? $method->getDeclaringClass()->getShortName() : $className;
        if ($className !== $declared) {
            return new StringList([$className, $declared, 'action', 'download']);
        }
        return new StringList([$className, 'action', 'download']);
    }

    /**
     * @param ReflectionClass<object> $class
     */
    public static function getRouteAttributes(ReflectionClass $class, ?ReflectionMethod $method = null): array
    {
        return
        [
            ContextConstants::GET_OBJECT => true,
            ContextConstants::RESOURCE_METHOD => true,
            ContextConstants::RESOURCE_NAME => $class->name,
            ContextConstants::METHOD_CLASS => $method->getDeclaringClass()->name,
            ContextConstants::METHOD_NAME => $method->name,
            ContextConstants::DISPLAY_FORM => true,
        ];
    }
}
