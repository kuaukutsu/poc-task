<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use PHPUnit\Framework\TestCase;
use kuaukutsu\poc\task\EntityWrapper;
use kuaukutsu\poc\task\EntityWrapperCollection;
use kuaukutsu\poc\task\tests\stub\IncreaseNumberStageStub;

final class EntityWrapperTest extends TestCase
{
    public function testSerialize(): void
    {
        $wrapper = new EntityWrapper(
            class: IncreaseNumberStageStub::class,
            params: [
                'name' => 'test',
                'number' => 1,
            ],
        );

        $serialize = serialize($wrapper);
        self::assertNotEmpty($serialize);

        $object = unserialize($serialize);
        self::assertInstanceOf(EntityWrapper::class, $object);
    }

    public function testCollection(): void
    {
        $wrapper = new EntityWrapper(
            class: IncreaseNumberStageStub::class,
            params: [
                'name' => 'test',
                'number' => 10,
            ],
        );

        $collection = new EntityWrapperCollection($wrapper);
        self::assertCount(1, $collection);

        $collection2 = new EntityWrapperCollection($wrapper);
        self::assertEquals($collection->getChecksum(), $collection2->getChecksum());

        $collection3 = new EntityWrapperCollection(clone $wrapper);
        self::assertNotEquals($collection->getChecksum(), $collection3->getChecksum());
    }
}
