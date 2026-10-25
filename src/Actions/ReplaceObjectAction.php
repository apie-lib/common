<?php
namespace Apie\Common\Actions;

use Apie\Common\Events\ApieResourceCreated;
use Apie\Common\Exceptions\EntityEtagConflictException;
use Apie\Common\IntegrationTestLogger;
use Apie\Common\Other\LockUtil;
use Apie\Core\Actions\ActionInterface;
use Apie\Core\Actions\ActionResponse;
use Apie\Core\Actions\ActionResponseStatus;
use Apie\Core\Actions\ActionResponseStatusList;
use Apie\Core\Actions\ApieFacadeInterface;
use Apie\Core\Attributes\Description;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Core\Datalayers\ApieDatalayer;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Enums\RequestMethod;
use Apie\Core\Exceptions\EntityAlreadyPersisted;
use Apie\Core\Exceptions\EntityNotFoundException;
use Apie\Core\Exceptions\IndexNotFoundException;
use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\Lists\StringList;
use Apie\Core\Utils\EntityUtils;
use Apie\Core\ValueObjects\Utils;
use Exception;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;

/**
 * Action to create/replace a new object.
 */
final class ReplaceObjectAction implements ActionInterface
{
    public function __construct(private readonly ApieFacadeInterface $apieFacade)
    {
    }

    public static function isAuthorized(ApieContext $context, bool $runtimeChecks, bool $throwError = false): bool
    {
        $refl = new ReflectionClass($context->getContext(ContextConstants::RESOURCE_NAME, $throwError));
        if (!$context->appliesToContext($refl, $runtimeChecks, $throwError ? new LogicException('Class access is not allowed') : null)) {
            return false;
        }
        if (EntityUtils::isPolymorphicEntity($refl) && $runtimeChecks && $context->hasContext(ContextConstants::RAW_CONTENTS)) {
            $rawContents = Utils::toArray($context->getContext(ContextConstants::RAW_CONTENTS, $throwError));
            try {
                $refl = EntityUtils::findClass($rawContents, $refl);
            } catch (IndexNotFoundException) {
            }
        }
        $constructor = $refl->getConstructor();
        if ($constructor && !$context->appliesToContext($constructor, $runtimeChecks, $throwError ? new LogicException('Class instantiation is not allowed') : null)) {
            return false;
        }
        return true;
    }
    
    /**
     * @param array<string|int, mixed> $rawContents
     */
    public function __invoke(ApieContext $context, array $rawContents): ActionResponse
    {
        if ($context->hasContext(ContextConstants::RESOURCE_ID)) {
            $rawContents['id'] = $context->getContext(ContextConstants::RESOURCE_ID);
        }

        $context->withContext(ContextConstants::RAW_CONTENTS, $rawContents)
            ->withContext(ContextConstants::APIE_ACTION, __CLASS__)
            ->checkAuthorization();
        try {
            $resource = $this->apieFacade->denormalizeNewObject(
                $rawContents,
                $context->getContext(ContextConstants::RESOURCE_NAME),
                $context
            );
        } catch (Exception $error) {
            IntegrationTestLogger::logException($error);
            return ActionResponse::createClientError($this->apieFacade, $context, $error);
        }

        if (!$resource instanceof EntityInterface) {
            throw new InvalidTypeException($resource, EntityInterface::class);
        }
        $request = $context->getContext(ServerRequestInterface::class, false);
        $ifMatch = $request instanceof ServerRequestInterface && $request->hasHeader('If-Match')
            ? trim($request->getHeaderLine('If-Match'))
            : null;

        $context = $context->withContext(ContextConstants::RESOURCE, $resource);
        $boundedContextId = new BoundedContextId($context->getContext(ContextConstants::BOUNDED_CONTEXT_ID));
        $request = $context->getContext(ServerRequestInterface::class, false);
        $ifMatch = $request instanceof ServerRequestInterface && $request->hasHeader('If-Match')
            ? trim($request->getHeaderLine('If-Match'))
            : null;
        $putContext = $context
            ->withContext(ContextConstants::DISABLE_CONTEXT_FILTER, true);
        $lock = LockUtil::createLock(
            $putContext,
            [ContextConstants::BOUNDED_CONTEXT_ID, ContextConstants::RESOURCE_NAME, ContextConstants::RESOURCE_ID],
            write: true
        );
        try {
            $existing = null;
            try {
                $existing = $this->apieFacade->find($resource->getId(), $boundedContextId);
            } catch (EntityNotFoundException) {
            }
            if ($existing === null) {
                if ($ifMatch !== null) {
                    throw new EntityEtagConflictException();
                }
            } else {
                $resourceContext = $putContext
                    ->withContext(ContextConstants::RESOURCE, $existing)
                    ->withContext(ContextConstants::APIE_ACTION, GetItemAction::class);
                if (!GetItemAction::isAuthorized($resourceContext, true)) {
                    throw new EntityAlreadyPersisted($resource);
                }
                if ($ifMatch !== null) {
                    $metadata = $context->getContext(ApieDatalayer::class)
                        ->getMetadata($resource->getId(), $boundedContextId);
                    $currentEtag = '"' . $metadata->etag . '"';
                    $providedEtags = array_map('trim', explode(',', $ifMatch));
                    if (!in_array('*', $providedEtags, true) && !in_array($currentEtag, $providedEtags, true)) {
                        throw new EntityEtagConflictException();
                    }
                }
            }
            $resource = $this->apieFacade->upsert($resource, $boundedContextId);
        } catch (Exception $error) {
            IntegrationTestLogger::logException($error);
            return ActionResponse::createClientError($this->apieFacade, $context, $error);
        } finally {
            $lock->release();
        }
        $context = $context->withContext(ContextConstants::RESOURCE, $resource);
        $context->dispatchEvent(new ApieResourceCreated($resource, $context));
        return ActionResponse::createCreationSuccess($this->apieFacade, $context, $resource, $resource);
    }

    /**
     * @return ReflectionClass<EntityInterface>
     */
    public static function getInputType(ReflectionClass $class): ReflectionClass
    {
        return $class;
    }

    /**
     * @return ReflectionClass<EntityInterface>
     */
    public static function getOutputType(ReflectionClass $class): ReflectionClass
    {
        return $class;
    }

    public static function getPossibleActionResponseStatuses(): ActionResponseStatusList
    {
        return new ActionResponseStatusList([
            ActionResponseStatus::CREATED,
            ActionResponseStatus::CLIENT_ERROR,
            ActionResponseStatus::PERISTENCE_ERROR
        ]);
    }

    /**
     * @param ReflectionClass<covariant object> $class
     */
    public static function getDescription(ReflectionClass $class): string
    {
        $description = 'Creates an instance of ' . $class->getShortName();
        foreach ($class->getAttributes(Description::class) as $attribute) {
            $description .= '. ' . $attribute->newInstance()->description;
        }

        return $description;
    }
    
    /**
     * @param ReflectionClass<covariant object> $class
     */
    public static function getTags(ReflectionClass $class): StringList
    {
        return new StringList([$class->getShortName(), 'resource']);
    }

    /**
     * @param ReflectionClass<covariant object> $class
     */
    public static function getRouteAttributes(ReflectionClass $class): array
    {
        return [
            ContextConstants::REPLACE_OBJECT => true,
            ContextConstants::RESOURCE_NAME => $class->name,
            ContextConstants::DISPLAY_FORM => true,
        ];
    }
}
