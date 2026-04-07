<?php
namespace Apie\Tests\Common\Other;

use Apie\Common\Other\EntitySnapshot;
use Apie\Common\Other\EntitySnapshotFieldMap;
use Apie\Common\Other\EntitySnapshotFile;
use Apie\Common\Other\EntitySnapshotLeaf;
use Apie\Core\Attributes\AnyApplies;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\FileStorage\StoredFile;
use Apie\Core\ValueObjects\DatabaseText;
use Apie\Fixtures\Entities\CollectionItemOwned;
use Apie\Fixtures\Entities\ImageFile;
use Apie\Fixtures\Entities\Polymorphic\AnimalIdentifier;
use Apie\Fixtures\Entities\Polymorphic\Cow;
use Apie\Fixtures\Entities\UserWithAddress;
use Apie\Fixtures\Identifiers\CollectionItemOwnedIdentifier;
use Apie\Fixtures\Identifiers\ImageFileIdentifier;
use Apie\Fixtures\ValueObjects\AddressWithZipcodeCheck;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EntitySnapshotTest extends TestCase
{
    #[DataProvider('someDataProvider')]
    #[Test]
    public function it_can_create_a_snapshot_from_some_data(mixed $expected, EntityInterface $input)
    {
        $snapshot = EntitySnapshot::createFrom($input);
        $this->assertEquals(
            $expected,
            $snapshot
        );
    }

    public static function someDataProvider(): \Generator
    {
        $id = CollectionItemOwnedIdentifier::createRandom();
        yield 'Simple entity' => [
            new EntitySnapshot(
                new EntitySnapshotFieldMap([
                    'id' => new EntitySnapshotLeaf($id->toNative(), new AnyApplies()),
                    'owned' => new EntitySnapshotLeaf(true, new AnyApplies()),
                ]),
                new AnyApplies()
            ),
            new CollectionItemOwned(
                $id,
                new UserWithAddress(
                    new AddressWithZipcodeCheck(
                        new DatabaseText('Evergreen terrace'),
                        new DatabaseText('742'),
                        new DatabaseText('11111'),
                        new DatabaseText('Springfield')
                    )
                ),
                true
            )
        ];

        $id = ImageFileIdentifier::createRandom();
        yield 'File upload' => [
            new EntitySnapshot(
                new EntitySnapshotFieldMap([
                    'id' => new EntitySnapshotLeaf($id->toNative(), new AnyApplies()),
                    'file' => new EntitySnapshotFile(null, 'hello-world.txt', new AnyApplies()),
                    'alternativeText' => new EntitySnapshotLeaf('Alt text', new AnyApplies()),
                ]),
                new AnyApplies()
            ),
            new ImageFile(
                $id,
                StoredFile::createFromString('Hello world', clientOriginalFile: 'hello-world.txt'),
                'Alt text'
            )
        ];

        $id = AnimalIdentifier::createRandom();
        yield 'Polymorphic entity' => [
            new EntitySnapshot(
                new EntitySnapshotFieldMap([
                    'id' => new EntitySnapshotLeaf($id->toNative(), new AnyApplies()),
                    'animalType' => new EntitySnapshotLeaf('cow', new AnyApplies()),
                    'hasMilk' => new EntitySnapshotLeaf(false, new AnyApplies()),
                ]),
                new AnyApplies()
            ),
            new Cow($id)
        ];
    }
}
