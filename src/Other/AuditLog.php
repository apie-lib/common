<?php
namespace Apie\Common\Other;

use Apie\Core\Attributes\AlwaysDisabled;
use Apie\Core\Attributes\Context;
use Apie\Core\Attributes\FakeCount;
use Apie\Core\Attributes\Not;
use Apie\Core\Attributes\Requires;
use Apie\Core\Attributes\StaticCheck;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\ValueObjects\EntityReference;
use Apie\Serializer\PropertySerializer\SerializedProperties;

#[FakeCount(0)]
class AuditLog implements EntityInterface
{
    private AuditLogIdentifier $id;

    #[StaticCheck(new AlwaysDisabled())]
    public function __construct(
        private readonly EntityReference $reference,
        #[Context()]
        private readonly SerializedProperties $serializedProperties,
    ) {
        $this->id = new AuditLogIdentifier($reference, microtime(true));
    }

    public function getId(): AuditLogIdentifier
    {
        return $this->id;
    }

    public function getReference(): EntityReference
    {
        return $this->reference;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRawData(): array
    {
        return $this->serializedProperties->jsonSerialize();
    }

    public function getData(): ?EntityInterface
    {
        // TODO
        return null;
    }
}
