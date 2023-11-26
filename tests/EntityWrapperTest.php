<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use kuaukutsu\poc\task\tests\stub\TestWrapperDto;
use kuaukutsu\poc\task\tests\stub\TestWrapperStageStub;
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

    public function testObjectSerialize(): void
    {
        $test = TestWrapperDto::hydrate(
            [
                'name' => 'test wrapper',
            ]
        );

        $wrapper = new EntityWrapper(
            class: TestWrapperStageStub::class,
            params: [
                'dto' => $test,
                'wrapper' => $test,
            ],
        );

        $serialize = serialize($wrapper);
        self::assertNotEmpty($serialize);

        $object = unserialize($serialize);
        self::assertInstanceOf(EntityWrapper::class, $object);

        /** @var EntityWrapper $object */
        self::assertInstanceOf(TestWrapperDto::class, $object->params['dto']);
        self::assertInstanceOf(TestWrapperDto::class, $object->params['wrapper']);
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

        $wrapperSimilar = new EntityWrapper(
            class: IncreaseNumberStageStub::class,
            params: [
                'name' => 'test',
                'number' => 11,
            ],
        );

        $collection = new EntityWrapperCollection($wrapper);
        self::assertCount(1, $collection);

        $collection2 = new EntityWrapperCollection($wrapper);
        self::assertEquals($collection->getChecksum(), $collection2->getChecksum());

        $collection3 = new EntityWrapperCollection(clone $wrapper);
        self::assertEquals($collection->getChecksum(), $collection3->getChecksum());

        $collection4 = new EntityWrapperCollection($wrapperSimilar);
        self::assertNotEquals($collection->getChecksum(), $collection4->getChecksum());
    }
}
