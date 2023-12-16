<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use Psr\Container\ContainerExceptionInterface;
use PHPUnit\Framework\TestCase;
use kuaukutsu\poc\task\processing\TaskProcessing;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\state\TaskStateSkip;
use kuaukutsu\poc\task\state\TaskFlag;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\service\TaskDestroyer;
use kuaukutsu\poc\task\service\TaskExecutor;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\EntityWrapper;
use kuaukutsu\poc\task\EntityNode;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\TaskManagerOptions;
use kuaukutsu\poc\task\TaskBuilder;
use kuaukutsu\poc\task\tests\service\StubNode;
use kuaukutsu\poc\task\tests\stub\TestStageStub;

final class ProcessingSkipTest extends TestCase
{
    use Container;

    private readonly EntityTask $task;

    private readonly EntityNode $node;

    private readonly TaskQuery $taskQuery;

    private readonly StageQuery $stageQuery;

    private readonly TaskProcessing $processing;

    private readonly TaskDestroyer $destroyer;

    private readonly TaskBuilder $builder;

    private readonly TaskExecutor $taskExecutor;

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
        $this->taskExecutor = self::get(TaskExecutor::class);
        $this->builder = self::get(TaskBuilder::class);
        $this->node = self::get(StubNode::class);

        $this->options = new TaskManagerOptions(
            bindir: __DIR__ . '/bin',
            heartbeat: 5.,
            keeperInterval: 1.,
        );
    }

    public function testLoadTaskProcess(): void
    {
        $flag = new TaskFlag();
        $uuid = new EntityUuid($this->task->getUuid());

        $task = $this->taskQuery->getOne($uuid);
        self::assertEquals($flag->unset()->setSkipped()->toValue(), $task->flag);

        self::assertFalse($this->processing->hasTaskProcess());
        $this->processing->loadTaskProcess($this->options);
        self::assertFalse($this->processing->hasTaskProcess());

        foreach ($this->stageQuery->iterableByTask($uuid) as $stage) {
            self::assertEquals($flag->unset()->setReady()->toValue(), $stage->flag);
        }

        $this->taskExecutor->run($this->task);

        $task = $this->taskQuery->getOne($uuid);
        self::assertEquals($flag->unset()->setReady()->toValue(), $task->flag);

        $this->processing->loadTaskProcess($this->options);
        self::assertTrue($this->processing->hasTaskProcess());
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

        $draft->setState(
            new TaskStateSkip(
                new TaskStateMessage('Task Skipped test')
            )
        );

        $this->task = $this->builder->build($this->node, $draft);
    }

    protected function tearDown(): void
    {
        $this->destroyer->purge(
            new EntityUuid($this->task->getUuid())
        );
    }
}
