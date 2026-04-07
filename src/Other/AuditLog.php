<?php
namespace Apie\Common\Other;

use Apie\Core\Attributes\AlwaysDisabled;
use Apie\Core\Attributes\Context;
use Apie\Core\Attributes\FakeCount;
use Apie\Core\Attributes\StaticCheck;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Lists\PermissionList;
use Apie\Core\Permissions\RequiresPermissionsInterface;
use Apie\Core\ValueObjects\IdFriendlyEntityReference;
use Apie\Serializer\ValueObjects\SerializedPhpObject;

#[FakeCount(0)]
class AuditLog implements EntityInterface, RequiresPermissionsInterface
{
    private AuditLogIdentifier $id;

    private PermissionList $permissionSnapshot;

    private EntitySnapshotInstance $snapshot;

    #[StaticCheck(new AlwaysDisabled())]
    public function __construct(
        private readonly IdFriendlyEntityReference $reference,
        #[Context()]
        private readonly SerializedPhpObject $serializedProperties,
    ) {
        $this->id = new AuditLogIdentifier($reference, microtime(true));
        $object = $serializedProperties->toPhpObject();

        $this->snapshot = EntitySnapshot::createFrom($object);
        $this->permissionSnapshot = $object instanceof RequiresPermissionsInterface ? $object->getRequiredPermissions() : new PermissionList();
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
