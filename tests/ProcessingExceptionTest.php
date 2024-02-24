<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use Symfony\Component\Process\Process;
use Psr\Container\ContainerExceptionInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use kuaukutsu\poc\task\processing\TaskProcessing;
use kuaukutsu\poc\task\processing\TaskProcess;
use kuaukutsu\poc\task\state\TaskFlag;
use kuaukutsu\poc\task\state\TaskStateError;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\service\TaskDestroyer;
use kuaukutsu\poc\task\EntityWrapper;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\TaskManagerOptions;
use kuaukutsu\poc\task\TaskBuilder;
use kuaukutsu\poc\task\tests\stub\TestStageStub;

final class ProcessingExceptionTest extends TestCase
{
    use Container;

    private readonly EntityTask $task;

    private readonly TaskQuery $taskQuery;

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
        $this->destroyer = self::get(TaskDestroyer::class);
        $this->processing = self::get(TaskProcessing::class);
        $this->builder = self::get(TaskBuilder::class);

        $this->options = new TaskManagerOptions(
            bindir: __DIR__ . '/bin',
            heartbeat: 5.,
            keeperInterval: 1.,
        );
    }

    /**
     * @throws Exception
     */
    public function testExceptionTaskProcess(): void
    {
        $this->processing->loadTaskProcess($this->options);
        self::assertTrue($this->processing->hasTaskProcess());

        $context = $this->processing->getTaskProcess();

        $flag = new TaskFlag();
        $task = $this->taskQuery->getOne(new EntityUuid($context->task));
        self::assertEquals($flag->setRunning()->toValue(), $task->flag);

        $this->processing->next(
            new TaskProcess(
                $context->getHash(),
                $context->task,
                $context->stage,
                $this->getProcess(
                    new TaskStateError(
                        new TaskStateMessage('error')
                    )
                ),
            )
        );

        self::assertFalse($this->processing->hasTaskProcess());

        $task = $this->taskQuery->getOne(new EntityUuid($context->task));
        self::assertEquals($flag->setCanceled()->toValue(), $task->flag);
    }

    protected function setUp(): void
    {
        $draft = $this->builder->create(
            'task test builder',
            new EntityWrapper(
                class: TestStageStub::class,
                params: [
                    'name' => 'Test initialization.',
                ],
            ),
        );

        $this->task = $this->builder->build($draft);
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
            ->willReturn(false);

        $stub->method('getOutput')
            ->willReturn(
                serialize($state)
            );

        return $stub;
    }
}
