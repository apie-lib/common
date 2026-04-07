<?php
namespace Apie\Common\Other;

use Apie\Common\Enums\AccessDenied;
use Apie\Core\Attributes\AnyApplies;
use Apie\Core\Attributes\ApieContextAttribute;
use Apie\Core\Attributes\RuntimeCheck;
use Apie\Core\Context\ApieContext;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\FileStorage\StoredFile;
use Apie\Core\Metadata\Fields\FieldInterface;
use Apie\Core\Metadata\GetterInterface;
use Apie\Core\Metadata\MetadataFactory;
use Apie\Core\ValueObjects\Interfaces\ValueObjectInterface;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Lists\SerializedHashmap;
use Apie\TypeConverter\ReflectionTypeFactory;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionAttribute;
use ReflectionClass;
use SensitiveParameter;

/**
 * Snapshot for audit log. Represents an object. It stored the field data and permission metadata.
 */
final class EntitySnapshot implements EntitySnapshotInstance
{
    public function __construct(
        private readonly EntitySnapshotFieldMap $mapping,
        private readonly ApieContextAttribute $context
    ) {
    }

    public function applies(ApieSerializerContext $apieSerializerContext): bool
    {
        return $this->context->applies($apieSerializerContext->getContext());
    }

    public function normalize(ApieSerializerContext $apieSerializerContext): SerializedHashmap|AccessDenied
    {
        if (!$this->applies($apieSerializerContext)) {
            return AccessDenied::Denied;
        }
        $map = [];
        foreach ($this->mapping as $fieldName => $fieldData) {
            $subcontext = $apieSerializerContext->visit($fieldName);
            if ($fieldData->applies($subcontext)) {
                $map[$fieldName] = $fieldData->normalize($subcontext);
            }
        }
        return new SerializedHashmap($map);
    }

    public static function createFrom(EntityInterface $entity): self
    {
        $refl = new ReflectionClass($entity);
        $apieContext = new ApieContext([]);
        $metadata = MetadataFactory::getResultMetadata($refl, $apieContext);
        $attributes = array_map(
            fn (ReflectionAttribute $attr) => $attr->newInstance(),
            $refl->getAttributes(RuntimeCheck::class)
        );
        $data = [];
        foreach ($metadata->getHashmap()->filterOnContext($apieContext, getters: true) as $name => $field) {
            if ($field->isField()) {
                $data[$name] = self::createFromField($entity, $apieContext, $field);
            }
        }
        return new EntitySnapshot(
            new EntitySnapshotFieldMap($data),
            new AnyApplies(...$attributes)
        );
    }

    public static function createFromField(object $object, ApieContext $apieContext, GetterInterface&FieldInterface $fieldData): EntitySnapshot|EntitySnapshotFile|EntitySnapshotLeaf|EntitySnapshotHidden
    {
        $value = $fieldData->getValue($object, $apieContext);
        if ($value instanceof ValueObjectInterface) {
            $value = $value->toNative();
        }
        
        $context =  new AnyApplies(...$fieldData->getAttributes(RuntimeCheck::class));
        if ($fieldData->getAttributes(SensitiveParameter::class)) {
            return new EntitySnapshotHidden($context);
        }
        if ($value instanceof UploadedFileInterface) {
            $storagePath = null;
            $originalFilename = $value->getClientFilename();
            if ($value instanceof StoredFile) {
                $storagePath = $value->getStoragePath();
            }
            return new EntitySnapshotFile(
                $storagePath,
                $originalFilename,
                new AnyApplies(...$fieldData->getAttributes(RuntimeCheck::class))
            );
        }
        if (is_scalar($value)) {
            return new EntitySnapshotLeaf($value, $context);
        }
        
        $metadata = MetadataFactory::getResultMetadata(
            ReflectionTypeFactory::createReflectionType(get_debug_type($value)),
            $apieContext
        );
    
        $data = [];
        foreach ($metadata->getHashmap()->filterOnContext($apieContext, getters: true) as $name => $field) {
            if ($field->isField()) {
                $data[$name] = self::createFromField($value, $apieContext, $field);
            }
        }
        return new EntitySnapshot(
            new EntitySnapshotFieldMap($data),
            $context
        );
    }
}
