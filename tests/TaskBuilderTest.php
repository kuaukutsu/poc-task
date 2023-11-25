<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use LogicException;
use Psr\Container\ContainerExceptionInterface;
use PHPUnit\Framework\TestCase;
use kuaukutsu\poc\task\state\TaskStateReady;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\service\TaskDestroyer;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\EntityWrapper;
use kuaukutsu\poc\task\TaskBuilder;
use kuaukutsu\poc\task\tests\stub\IncreaseNumberStageStub;
use kuaukutsu\poc\task\tests\stub\TestStageStub;

final class TaskBuilderTest extends TestCase
{
    use Container;

    private TaskBuilder $builder;

    public function testDraftBuilder(): void
    {
        $draft = $this->builder->create(
            'task test builder',
            new EntityWrapper(
                class: IncreaseNumberStageStub::class,
                params: [
                    'name' => 'Number initialization.',
                ],
            ),
        );

        self::assertEquals('task test builder', $draft->title);
        self::assertCount(1, $draft->stages);

        $draft->addStage(
            new EntityWrapper(
                class: IncreaseNumberStageStub::class,
                params: [
                    'name' => 'Number initialization.',
                ],
            )
        );

        self::assertCount(2, $draft->stages);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testCreate(): void
    {
        $draft = $this->builder->create(
            'task test builder',
            new EntityWrapper(
                class: IncreaseNumberStageStub::class,
                params: [
                    'name' => 'Number initialization.',
                ],
            ),
        );

        $task = $this->builder->build($draft);
        self::assertEquals($draft->title, $task->getTitle());
        self::assertEquals(new TaskStateReady(), $task->getState());

        self::get(TaskDestroyer::class)->purge(
            new EntityUuid($task->getUuid())
        );
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testCreateChecksum(): void
    {
        $draft = $this->builder->create(
            'checksum',
            new EntityWrapper(
                class: TestStageStub::class,
                params: [
                    'name' => 'Test initialization.',
                ],
            ),
        );

        $draftSimilar = $this->builder->create(
            'checksum',
            new EntityWrapper(
                class: TestStageStub::class,
                params: [
                    'name' => 'Test progressive.',
                ],
            ),
        );

        $draftAnotherSimilar = $this->builder->create(
            'checksum another',
            new EntityWrapper(
                class: TestStageStub::class,
                params: [
                    'name' => 'Test progressive.',
                ],
            ),
        );

        $query = self::get(TaskQuery::class);

        $tasks = [];
        $tasks[] = $this->builder->build($draft);
        self::assertTrue($query->existsChecksum($draft->getChecksum()));

        $tasks[] = $this->builder->build($draftSimilar);
        self::assertTrue($query->existsChecksum($draftSimilar->getChecksum()));

        $tasks[] = $this->builder->build($draftAnotherSimilar);
        self::assertTrue($query->existsChecksum($draftAnotherSimilar->getChecksum()));

        $destroyer = self::get(TaskDestroyer::class);
        foreach ($tasks as $task) {
            $destroyer->purge(
                new EntityUuid($task->getUuid())
            );
        }
    }

    public function testCreateChecksumFailure(): void
    {
        $draft = $this->builder->create(
            'checksum',
            new EntityWrapper(
                class: TestStageStub::class,
                params: [
                    'name' => 'Test initialization.',
                ],
            ),
        );

        $draftDuplicate = $this->builder->create(
            'checksum',
            new EntityWrapper(
                class: TestStageStub::class,
                params: [
                    'name' => 'Test initialization.',
                ],
            ),
        );

        $this->builder->build($draft);

        $this->expectException(LogicException::class);
        $this->builder->build($draftDuplicate);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    protected function setUp(): void
    {
        $this->builder = self::get(TaskBuilder::class);
    }
}
