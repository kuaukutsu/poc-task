<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use PHPUnit\Framework\MockObject\Exception;
use Psr\Container\ContainerExceptionInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use kuaukutsu\poc\task\handler\StageHandler;
use kuaukutsu\poc\task\processing\TaskProcess;
use kuaukutsu\poc\task\processing\TaskProcessing;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\state\TaskStateSuccess;
use kuaukutsu\poc\task\state\TaskStateWaiting;
use kuaukutsu\poc\task\state\TaskFlag;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\service\TaskDestroyer;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\EntityWrapper;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\TaskManagerOptions;
use kuaukutsu\poc\task\TaskBuilder;
use kuaukutsu\poc\task\tests\stub\TestContextResponseStageStub;
use kuaukutsu\poc\task\tests\stub\TestHandlerStageStub;
use kuaukutsu\poc\task\tests\service\Storage;

final class ProcessingPromiseTest extends TestCase
{
    use Container;

    private readonly EntityTask $task;

    private readonly TaskQuery $taskQuery;

    private readonly StageQuery $stageQuery;

    private readonly StageHandler $handler;

    private readonly TaskProcessing $processing;

    private readonly TaskDestroyer $destroyer;

    private readonly TaskBuilder $builder;

    private readonly TaskManagerOptions $options;

    /**
     * @throws ContainerExceptionInterface
     */
    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->taskQuery = self::get(TaskQuery::class);
        $this->stageQuery = self::get(StageQuery::class);
        $this->destroyer = self::get(TaskDestroyer::class);
        $this->processing = self::get(TaskProcessing::class);
        $this->builder = self::get(TaskBuilder::class);
        $this->handler = self::get(StageHandler::class);

        $this->options = new TaskManagerOptions(
            bindir: __DIR__ . '/bin',
            heartbeat: 5.,
            keeperInterval: 1.,
        );
    }

    /**
     * @throws Exception
     */
    public function testLoadingPromise(): void
    {
        $flag = new TaskFlag();
        $uuid = new EntityUuid($this->task->getUuid());

        $this->processing->loadTaskProcess($this->options);
        self::assertTrue($this->processing->hasTaskProcess());

        $task = $this->taskQuery->getOne($uuid);
        self::assertEquals($flag->unset()->setRunning()->toValue(), $task->flag);

        $context = $this->processing->getTaskProcess();
        $contextStageOne = $context->stage;
        $exitCode = $this->handler->handle($context->stage);
        self::assertEquals(0, $exitCode);

        $num = 0;
        foreach ($this->stageQuery->iterableByTask($uuid) as $stage) {
            $num++;
            if ($num === 1) {
                self::assertEquals($flag->unset()->setWaiting()->toValue(), $stage->flag);
                continue;
            }

            self::assertEquals($flag->unset()->setReady()->toValue(), $stage->flag);
        }

        $this->processing->next(
            new TaskProcess(
                $context->task,
                $context->stage,
                $this->getProcess(
                    new TaskStateWaiting(
                        uuid: $context->stage,
                        task: $context->task,
                        message: new TaskStateMessage('Waiting')
                    )
                ),
            )
        );

        $this->processing->loadTaskProcess($this->options);
        // Смена контекста на вложенную задачу
        $contextNestedTask = $this->processing->getTaskProcess();
        self::assertNotEquals($context->task, $contextNestedTask->task);

        // Nested Task
        $nestedUuid = new EntityUuid($contextNestedTask->task);
        $task = $this->taskQuery->getOne($nestedUuid);
        self::assertEquals($flag->unset()->setRunning()->toValue(), $task->flag);

        // one stage
        $exitCode = $this->handler->handle($contextNestedTask->stage);
        self::assertEquals(0, $exitCode);
        $this->processing->next(
            new TaskProcess(
                $contextNestedTask->task,
                $contextNestedTask->stage,
                $this->getProcess(
                    new TaskStateSuccess(
                        new TaskStateMessage('Success')
                    )
                ),
            )
        );

        // two stage
        $contextNestedTask = $this->processing->getTaskProcess();
        $exitCode = $this->handler->handle($contextNestedTask->stage);
        self::assertEquals(0, $exitCode);
        $this->processing->next(
            new TaskProcess(
                $contextNestedTask->task,
                $contextNestedTask->stage,
                $this->getProcess(
                    new TaskStateSuccess(
                        new TaskStateMessage('Success')
                    )
                ),
            )
        );

        // completed
        $task = $this->taskQuery->getOne($nestedUuid);
        self::assertEquals($flag->unset()->setSuccess()->toValue(), $task->flag);

        $this->destroyer->purge($nestedUuid);

        // Возврат контекста
        $contextReturnTask = $this->processing->getTaskProcess();
        self::assertEquals($context->task, $contextReturnTask->task);
        $exitCode = $this->handler->handle($contextReturnTask->stage, $contextStageOne);
        self::assertEquals(0, $exitCode);
        $this->processing->next(
            new TaskProcess(
                $contextReturnTask->task,
                $contextReturnTask->stage,
                $this->getProcess(
                    new TaskStateSuccess(
                        new TaskStateMessage('Success')
                    )
                ),
            )
        );

        $this->processing->loadTaskProcess($this->options);
        self::assertFalse($this->processing->hasTaskProcess());

        // completed
        $task = $this->taskQuery->getOne($uuid);
        self::assertEquals($flag->unset()->setSuccess()->toValue(), $task->flag);
    }

    public static function setUpBeforeClass(): void
    {
        unlink(Storage::task->value);
        unlink(Storage::stage->value);
    }

    protected function setUp(): void
    {
        $this->task = $this->builder->build(
            $this->builder->create(
                'task test promise',
                new EntityWrapper(
                    class: TestHandlerStageStub::class,
                ),
                new EntityWrapper(
                    class: TestContextResponseStageStub::class,
                ),
            )
        );
    }

    protected function tearDown(): void
    {
        $this->destroyer->purge(
            new EntityUuid($this->task->getUuid())
        );
    }

    /**
     * @throws Exception
     */
    private function getProcess(TaskStateInterface $state): Process
    {
        $stub = $this->createMock(Process::class);

        $stub->method('isSuccessful')
            ->willReturn(true);

        $stub->method('getOutput')
            ->willReturn(
                serialize($state)
            );

        return $stub;
    }
}
