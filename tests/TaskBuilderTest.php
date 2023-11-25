<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use DI\DependencyException;
use DI\NotFoundException;
use PHPUnit\Framework\TestCase;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\state\TaskStateReady;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\EntityWrapper;
use kuaukutsu\poc\task\TaskBuilder;
use kuaukutsu\poc\task\tests\stub\IncreaseNumberStageStub;

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
     * @throws DependencyException
     * @throws NotFoundException
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

        $uuid = new EntityUuid($task->getUuid());
        self::get(StageCommand::class)->removeByTask($uuid);
        self::get(TaskCommand::class)->remove($uuid);
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function setUp(): void
    {
        $this->builder = self::get(TaskBuilder::class);
    }
}
