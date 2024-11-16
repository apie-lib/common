<?php
namespace Apie\Common\Actions;

use Apie\Common\IntegrationTestLogger;
use Apie\Common\Other\DownloadFile;
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
use Apie\Core\PropertyAccess;
use Apie\Core\ValueObjects\Exceptions\InvalidStringForValueObjectException;
use Apie\Serializer\Exceptions\ValidationException;
use LogicException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionClass;
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
        return $context->appliesToContext($refl, $runtimeChecks, $throwError ? new LogicException('Class access is not allowed!') : null);
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
        $properties = explode('/', $context->getContext('properties'));
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
        $result = PropertyAccess::getPropertyValue($resource, $properties, $context, false);
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

    /** @param ReflectionClass<object> $class */
    public static function getInputType(ReflectionClass $class, ?ReflectionMethod $method = null): ReflectionMethod
    {
        return $class->getConstructor() ?? new ReflectionMethod(DownloadFile::class, '__construct');
    }

    /** @param ReflectionClass<object> $class */
    public static function getOutputType(ReflectionClass $class, ?ReflectionMethod $method = null): ReflectionMethod
    {
        return new ReflectionMethod(DownloadFile::class, 'download');
    }

    public static function getPossibleActionResponseStatuses(?ReflectionMethod $method = null): ActionResponseStatusList
    {
        return new ActionResponseStatusList([
            ActionResponseStatus::SUCCESS,
            ActionResponseStatus::CLIENT_ERROR,
            ActionResponseStatus::NOT_FOUND,
        ]);
    }

    /**
     * @param ReflectionClass<object> $class
     */
    public static function getDescription(ReflectionClass $class, ?ReflectionMethod $method = null): string
    {
        return 'Streams a file on a ' . $class->getShortName() . ' with a specific id';
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
            ContextConstants::DISPLAY_FORM => true,
        ];
    }
}
