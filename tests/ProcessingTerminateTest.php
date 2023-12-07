<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use kuaukutsu\poc\task\state\TaskFlag;
use kuaukutsu\poc\task\processing\TaskProcessing;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\service\TaskDestroyer;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\TaskManagerOptions;
use kuaukutsu\poc\task\TaskBuilder;
use kuaukutsu\poc\task\tests\service\Storage;

final class ProcessingTerminateTest extends TestCase
{
    use Container;
    use TaskFaker;

    private readonly EntityTask $task;

    private readonly TaskQuery $taskQuery;

    private readonly StageQuery $stageQuery;

    private readonly TaskProcessing $processing;

    private readonly TaskDestroyer $destroyer;

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

        $this->options = new TaskManagerOptions(
            bindir: __DIR__ . '/bin',
            heartbeat: 5.,
            keeperInterval: 1.,
        );
    }

    public function testTerminate(): void
    {
        $flag = new TaskFlag();
        $uuid = new EntityUuid($this->task->getUuid());

        self::assertFalse($this->processing->hasTaskProcess());
        $this->processing->loadTaskProcess($this->options);
        self::assertTrue($this->processing->hasTaskProcess());

        $task = $this->taskQuery->getOne($uuid);
        self::assertEquals($flag->unset()->setRunning()->toValue(), $task->flag);

        $this->processing->terminate(SIGTERM);
        self::assertFalse($this->processing->hasTaskProcess());

        $task = $this->taskQuery->getOne($uuid);
        self::assertEquals($flag->unset()->setPaused()->toValue(), $task->flag);

        foreach ($this->stageQuery->iterableByTask($uuid) as $stage) {
            self::assertEquals($flag->unset()->setReady()->toValue(), $stage->flag);
        }

        $this->processing->loadTaskProcess($this->options);
        self::assertTrue($this->processing->hasTaskProcess());
    }

    public static function setUpBeforeClass(): void
    {
        unlink(Storage::task->value);
        unlink(Storage::stage->value);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    protected function setUp(): void
    {
        $this->task = $this->generateTask(
            self::get(TaskBuilder::class)
        );
    }

    protected function tearDown(): void
    {
        $this->destroyer->purge(
            new EntityUuid($this->task->getUuid())
        );
    }
}
