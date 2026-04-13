<?php
namespace Apie\Common\Other;

use Apie\Common\Enums\AuditLogEvent;
use Apie\Common\Other\Audit\AuditCreate;
use Apie\Common\Other\Audit\AuditEvent;
use Apie\Common\Other\Audit\AuditMethodCalled;
use Apie\Common\Other\Audit\AuditModified;
use Apie\Common\Other\Audit\AuditRead;
use Apie\Common\Other\Audit\AuditRemoved;
use Apie\Core\Attributes\AlwaysDisabled;
use Apie\Core\Attributes\Context;
use Apie\Core\Attributes\FakeCount;
use Apie\Core\Attributes\SearchFilterOption;
use Apie\Core\Attributes\StaticCheck;
use Apie\Core\Context\ApieContext;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Lists\PermissionList;
use Apie\Core\Permissions\RequiresPermissionsInterface;
use Apie\Core\Translator\ApieTranslatorInterface;
use Apie\Core\Translator\ValueObjects\TranslationString;
use Apie\Core\ValueObjects\IdFriendlyEntityReference;
use Apie\Core\ValueObjects\NonEmptyString;
use Apie\Core\ValueObjects\Utils;
use Apie\Serializer\ValueObjects\SerializedPhpObject;

#[FakeCount(0)]
class AuditLog implements EntityInterface, RequiresPermissionsInterface
{
    private AuditLogIdentifier $id;

    private PermissionList $permissionSnapshot;

    private EntitySnapshotInstance $snapshot;

    // separate properties for optimization in the database
    private readonly ?AuditCreate $createEvent;
    private readonly ?AuditModified $modifiedEvent;
    private readonly ?AuditRemoved $removedEvent;
    private readonly ?AuditRead $readEvent;
    private readonly ?AuditMethodCalled $methodCalledEvent;

    private const EVENT_MAPPING = [
        AuditCreate::class => 'createEvent',
        AuditModified::class => 'modifiedEvent',
        AuditRemoved::class => 'removedEvent',
        AuditRead::class => 'readEvent',
        AuditMethodCalled::class=> 'methodCalledEvent'
    ];

    #[StaticCheck(new AlwaysDisabled())]
    public function __construct(
        private readonly IdFriendlyEntityReference $reference,
        private readonly SerializedPhpObject $serializedProperties,
        AuditEvent $event,
        #[Context('_ignored')]
        private readonly ?SerializedPhpObject $createdBy = null,
    ) {
        $this->id = new AuditLogIdentifier($reference, microtime(true));
        $object = $serializedProperties->toPhpObject();

        $this->snapshot = EntitySnapshot::createFrom($object);
        $this->permissionSnapshot = $object instanceof RequiresPermissionsInterface ? $object->getRequiredPermissions() : new PermissionList();
        foreach (self::EVENT_MAPPING as $class => $property) {
            $this->$property = ($event instanceof $class) ? $event : null;
        }
    }

    #[SearchFilterOption(enabled: false)]
    public function getDescription(
        ApieTranslatorInterface $translator,
        ApieContext $context
    ): NonEmptyString {
        $context = $context->withContext(AuditLog::class, $this);
        foreach (self::EVENT_MAPPING as $property) {
            if ($this->$property !== null) {
                return $this->$property->getTranslation($translator, $context);
            }
        }
        return NonEmptyString::fromNative(
            $translator->getGeneralTranslation(
                $context,
                new TranslationString('audit_log.unknown_event')
            )
        );
    }

    public function getEvent(): AuditLogEvent
    {
        foreach (self::EVENT_MAPPING as $property) {
            if ($this->$property !== null) {
                return $this->$property->getEvent();
            }
        }
        throw new \LogicException('Unknown event type');
    }

    public function getCreatedBy(): ?NonEmptyString
    {
        $object = $this->createdBy?->toPhpObject();
        if ($object === null) {
            return null;
        }
        if ($object instanceof EntityInterface) {
            return NonEmptyString::fromNative(Utils::toString($object->getId()));
        }

        return NonEmptyString::fromNative(Utils::toString($object));

    }

    public function getRequiredPermissions(): PermissionList
    {
        $object = $this->serializedProperties->toPhpObject();
        if ($object instanceof RequiresPermissionsInterface) {
            return $object->getRequiredPermissions();
        }

        return $this->permissionSnapshot;
    }

    public function getId(): AuditLogIdentifier
    {
        return $this->id;
    }

    public function getReference(): IdFriendlyEntityReference
    {
        return $this->reference;
    }

    public function getData(): EntityInterface|EntitySnapshotInstance
    {
        $entity = $this->serializedProperties->toPhpObject();
        if ($entity instanceof EntityInterface) {
            return $entity;
        }
        return $this->snapshot;
    }
}
