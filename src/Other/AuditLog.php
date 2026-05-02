<?php
namespace Apie\Common\Other;

use Apie\Common\Enums\AuditLogEvent;
use Apie\Common\Other\Audit\AuditCreate;
use Apie\Common\Other\Audit\AuditEvent;
use Apie\Common\Other\Audit\AuditMethodCalled;
use Apie\Common\Other\Audit\AuditMigration;
use Apie\Common\Other\Audit\AuditModified;
use Apie\Common\Other\Audit\AuditRead;
use Apie\Common\Other\Audit\AuditRemoved;
use Apie\Core\ApieLib;
use Apie\Core\Attributes\AlwaysDisabled;
use Apie\Core\Attributes\Context;
use Apie\Core\Attributes\FakeCount;
use Apie\Core\Attributes\Policy;
use Apie\Core\Attributes\RuntimeCheck;
use Apie\Core\Attributes\SearchFilterOption;
use Apie\Core\Attributes\StaticCheck;
use Apie\Core\Attributes\StoreOptions;
use Apie\Core\Context\ApieContext;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Lists\PermissionList;
use Apie\Core\Lists\StringSet;
use Apie\Core\Permissions\RequiresPermissionsInterface;
use Apie\Core\Translator\ApieTranslatorInterface;
use Apie\Core\Translator\ValueObjects\TranslationString;
use Apie\Core\ValueObjects\IdFriendlyEntityReference;
use Apie\Core\ValueObjects\NonEmptyString;
use Apie\Core\ValueObjects\Utils;
use Apie\Serializer\ValueObjects\SerializedPhpObject;

#[FakeCount(0)]
#[StaticCheck(new Policy('staticCanViewAny', enabledOnMissingRule: true))]
#[RuntimeCheck(new Policy('canView', 'canViewAny'))]
class AuditLog implements EntityInterface, RequiresPermissionsInterface
{
    private AuditLogIdentifier $id;

    private StringSet $permissionSnapshot;

    #[StoreOptions(alwaysMixedData: true)]
    private EntitySnapshotInstance $snapshot;

    // separate properties for optimization in the database
    private readonly ?AuditCreate $createEvent;
    private readonly ?AuditModified $modifiedEvent;
    private readonly ?AuditRemoved $removedEvent;
    private readonly ?AuditRead $readEvent;
    private readonly ?AuditMethodCalled $methodCalledEvent;
    private readonly ?AuditMigration $migrationEvent;

    private const EVENT_MAPPING = [
        AuditCreate::class => 'createEvent',
        AuditModified::class => 'modifiedEvent',
        AuditRemoved::class => 'removedEvent',
        AuditRead::class => 'readEvent',
        AuditMethodCalled::class=> 'methodCalledEvent',
        AuditMigration::class => 'migrationEvent'
    ];

    #[StaticCheck(new AlwaysDisabled())]
    public function __construct(
        private readonly IdFriendlyEntityReference $reference,
        private readonly SerializedPhpObject $serializedProperties,
        AuditEvent $event,
        #[Context('_ignored')]
        private readonly ?SerializedPhpObject $createdBy = null,
    ) {
        $this->id = new AuditLogIdentifier((float) ApieLib::getPsrClock()->now()->format('U.u'), $reference);
        $object = $serializedProperties->toPhpObject();

        $this->snapshot = EntitySnapshot::createFrom($object);
        $this->permissionSnapshot = $object instanceof RequiresPermissionsInterface ? $object->getRequiredPermissions()->toStringList() : new StringSet();
        foreach (self::EVENT_MAPPING as $class => $property) {
            $this->$property = ($event instanceof $class) ? $event : null;
        }
    }

    #[SearchFilterOption(enabled: false)]
    #[StaticCheck(new Policy('staticReadDescription', enabledOnMissingRule: true))]
    #[RuntimeCheck(new Policy('readDescription'))]
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

    #[RuntimeCheck(new Policy('readEvent'))]
    #[StaticCheck(new Policy('staticReadEvent', enabledOnMissingRule: true))]
    public function getEvent(): AuditLogEvent
    {
        foreach (self::EVENT_MAPPING as $property) {
            if ($this->$property !== null) {
                return $this->$property->getEvent();
            }
        }
        throw new \LogicException('Unknown event type');
    }

    #[RuntimeCheck(new Policy('readCreatedBy'))]
    #[StaticCheck(new Policy('staticReadCreatedBy', enabledOnMissingRule: true))]
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

    #[RuntimeCheck(new Policy('readRequiredPermissions'))]
    #[StaticCheck(new Policy('staticReadRequiredPermissions', enabledOnMissingRule: true))]
    public function getRequiredPermissions(): PermissionList
    {
        $object = $this->serializedProperties->toPhpObject();
        if ($object instanceof RequiresPermissionsInterface) {
            return $object->getRequiredPermissions();
        }

        return new PermissionList($this->permissionSnapshot->toArray());
    }

    public function getId(): AuditLogIdentifier
    {
        return $this->id;
    }

    #[StaticCheck(new Policy('staticReadReference', enabledOnMissingRule: true))]
    #[RuntimeCheck(new Policy('readReference'))]
    public function getReference(): IdFriendlyEntityReference
    {
        return $this->reference;
    }

    #[StaticCheck(new Policy('staticReadData', enabledOnMissingRule: true))]
    #[RuntimeCheck(new Policy('readData'))]
    public function getData(): EntityInterface|EntitySnapshotInstance
    {
        $entity = $this->serializedProperties->toPhpObject();
        if ($entity instanceof EntityInterface) {
            return $entity;
        }
        return $this->snapshot;
    }

    #[StaticCheck(new Policy('staticReadSnapshot', enabledOnMissingRule: true))]
    #[RuntimeCheck(new Policy('readSnapshot'))]
    public function getSnapshot(): EntitySnapshotInstance
    {
        return $this->snapshot;
    }
}
