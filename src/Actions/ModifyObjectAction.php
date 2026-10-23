<?php
namespace Apie\Common\Actions;

use Apie\Common\Events\ApieResourceModified;
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
use Apie\Core\Exceptions\EntityNotFoundException;
use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\IdentifierUtils;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\StringList;
use Apie\Core\Metadata\MetadataFactory;
use Apie\Core\Utils\EntityUtils;
use Apie\Core\ValueObjects\Exceptions\InvalidStringForValueObjectException;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;

/**
 * Action to modify an existing object.
 */
final class ModifyObjectAction implements ActionInterface
{
    public function __construct(private readonly ApieFacadeInterface $apieFacade)
    {
    }

    public static function isAuthorized(ApieContext $context, bool $runtimeChecks, bool $throwError = false): bool
    {
        $refl = new ReflectionClass($context->getContext(ContextConstants::RESOURCE_NAME, $throwError));
        if (EntityUtils::isPolymorphicEntity($refl) && $runtimeChecks && $context->hasContext(ContextConstants::RESOURCE)) {
            $refl = new ReflectionClass($context->getContext(ContextConstants::RESOURCE, $throwError));
        }
        $metadata = MetadataFactory::getModificationMetadata($refl, $context);
        if ($metadata->getHashmap()->count() === 0) {
            if ($throwError) {
                throw new LogicException('Metadata for ' . $refl->getShortName() . ' has no fields to edit.');
            }
            return false;
        }
        return $context->appliesToContext($refl, $runtimeChecks, $throwError ? new LogicException('Operation on ' . $refl->getShortName() . ' is not allowed') : null);
    }
    
    /**
     * @param array<string|int, mixed> $rawContents
     */
    public function __invoke(ApieContext $context, array $rawContents): ActionResponse
    {
        $context->withContext(ContextConstants::APIE_ACTION, __CLASS__)->checkAuthorization();
        $resourceClass = new ReflectionClass($context->getContext(ContextConstants::RESOURCE_NAME));
        $id = $context->getContext(ContextConstants::RESOURCE_ID);
        if (!$resourceClass->implementsInterface(EntityInterface::class)) {
            throw new InvalidTypeException($resourceClass->name, 'EntityInterface');
        }
        $lock = LockUtil::createLock(
            $context,
            [ContextConstants::BOUNDED_CONTEXT_ID, ContextConstants::RESOURCE_NAME, ContextConstants::RESOURCE_ID],
            write: true
        );
        try {
            try {
                $identifier = IdentifierUtils::idStringToIdentifier($id, $context);
                $boundedContextId = new BoundedContextId($context->getContext(ContextConstants::BOUNDED_CONTEXT_ID));
                $resource = $this->apieFacade->find($identifier, $boundedContextId);
            } catch (InvalidStringForValueObjectException|EntityNotFoundException $error) {
                IntegrationTestLogger::logException($error);
                return ActionResponse::createClientError($this->apieFacade, $context, $error);
            }
            $request = $context->getContext(ServerRequestInterface::class, false);
            $ifMatch = $request instanceof ServerRequestInterface
                ? $request->getHeaderLine('If-Match')
                : '';
            if ($context->getContext(ContextConstants::REST_API, false) && $ifMatch !== '') {
                $metadata = $context->getContext(ApieDatalayer::class)
                    ->getMetadata($identifier, $boundedContextId);
                $currentEtag = '"' . $metadata->etag . '"';
                $providedEtags = array_map('trim', explode(',', $ifMatch));
                if (!in_array('*', $providedEtags, true) && !in_array($currentEtag, $providedEtags, true)) {
                    throw new EntityEtagConflictException();
                }
            }
            $context = $context->withContext(ContextConstants::RESOURCE, $resource);
            $resource = $this->apieFacade->denormalizeOnExistingObject(
                new ItemHashmap($rawContents),
                $resource,
                $context,
            );
            if (!$lock->isAcquired()) {
                throw new \LogicException('Lock was released before modification was finished!');
            }
            $resource = $this->apieFacade->persistExisting($resource, new BoundedContextId($context->getContext(ContextConstants::BOUNDED_CONTEXT_ID)));
        } catch (\Exception $error) {
            IntegrationTestLogger::logException($error);
            return ActionResponse::createClientError($this->apieFacade, $context, $error);
        } finally {
            $lock->release();
        }
        $context->dispatchEvent(new ApieResourceModified($resource, $context));
        return ActionResponse::createRunSuccess($this->apieFacade, $context, $resource, $resource);
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
            ActionResponseStatus::SUCCESS,
            ActionResponseStatus::CLIENT_ERROR,
            ActionResponseStatus::PERISTENCE_ERROR
        ]);
    }

    /**
     * @param ReflectionClass<covariant object> $class
     */
    public static function getDescription(ReflectionClass $class): string
    {
        $description = 'Modifies an instance of ' . $class->getShortName();
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
            ContextConstants::EDIT_OBJECT => true,
            ContextConstants::RESOURCE_NAME => $class->name,
            ContextConstants::DISPLAY_FORM => true,
        ];
    }
}
