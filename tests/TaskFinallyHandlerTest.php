<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use kuaukutsu\poc\task\handler\TaskFinallyHandler;
use kuaukutsu\poc\task\service\TaskDestroyer;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\state\TaskStateSuccess;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\EntityWrapper;
use kuaukutsu\poc\task\TaskBuilder;
use kuaukutsu\poc\task\tests\service\BaseStorage;
use kuaukutsu\poc\task\tests\stub\TestExceptionFinally;
use kuaukutsu\poc\task\tests\stub\TestParamsFinally;
use kuaukutsu\poc\task\tests\stub\TestFinally;
use kuaukutsu\poc\task\tests\stub\TestStageStub;

final class TaskFinallyHandlerTest extends TestCase
{
    use Container;

    private readonly EntityTask $task;

    private readonly TaskFinallyHandler $taskFinallyHandler;

    private readonly TaskDestroyer $destroyer;

    private readonly TaskBuilder $builder;

    private readonly BaseStorage $storage;

    /**
     * @throws ContainerExceptionInterface
     */
    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->destroyer = self::get(TaskDestroyer::class);
        $this->builder = self::get(TaskBuilder::class);
        $this->taskFinallyHandler = self::get(TaskFinallyHandler::class);
        $this->storage = self::get(BaseStorage::class);
    }

    public function testHandler(): void
    {
        $this->task = $this->builder->build(
            $this->builder
                ->create(
                    'task finally builder',
                    new EntityWrapper(
                        class: TestStageStub::class,
                        params: [
                            'name' => 'Test initialization.',
                        ],
                    ),
                )
                ->setFinally(
                    TestFinally::class
                )
        );

        $this->taskFinallyHandler->handle(
            $this->task->getUuid(),
            $this->task->getOptions(),
            new TaskStateSuccess(
                new TaskStateMessage('test finally')
            ),
        );

        self::assertEquals('test finally', $this->storage->get($this->task->getUuid()));

        $this->storage->unset($this->task->getUuid());
        self::assertEmpty($this->storage->get($this->task->getUuid()));
    }

    public function testParamsHandler(): void
    {
        $this->task = $this->builder->build(
            $this->builder
                ->create(
                    'task finally builder',
                    new EntityWrapper(
                        class: TestStageStub::class,
                        params: [
                            'name' => 'Test initialization.',
                        ],
                    ),
                )
                ->setFinally(
                    TestParamsFinally::class,
                    [
                        'name' => 'prefix',
                    ]
                )
        );

        $this->taskFinallyHandler->handle(
            $this->task->getUuid(),
            $this->task->getOptions(),
            new TaskStateSuccess(
                new TaskStateMessage('test finally')
            ),
        );

        self::assertEquals('prefixtest finally', $this->storage->get($this->task->getUuid()));

        $this->storage->unset($this->task->getUuid());
        self::assertEmpty($this->storage->get($this->task->getUuid()));
    }

    public function testHandlerException(): void
    {
        $this->task = $this->builder->build(
            $this->builder
                ->create(
                    'task finally builder',
                    new EntityWrapper(
                        class: TestStageStub::class,
                        params: [
                            'name' => 'Test initialization.',
                        ],
                    ),
                )
                ->setFinally(
                    TestExceptionFinally::class
                )
        );

        $this->taskFinallyHandler->handle(
            $this->task->getUuid(),
            $this->task->getOptions(),
            new TaskStateSuccess(
                new TaskStateMessage('test finally')
            ),
        );

        self::assertEmpty($this->storage->get($this->task->getUuid()));
    }

    protected function tearDown(): void
    {
        $this->destroyer->purge(
            new EntityUuid($this->task->getUuid())
        );
    }
}
