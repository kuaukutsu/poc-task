<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use Psr\Container\ContainerExceptionInterface;
use PHPUnit\Framework\TestCase;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\service\TaskDestroyer;
use kuaukutsu\poc\task\service\TaskViewer;
use kuaukutsu\poc\task\state\TaskFlag;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\TaskBuilder;

final class TaskViewerTest extends TestCase
{
    use Container;
    use TaskFaker;

    private readonly EntityTask $task;

    private readonly TaskViewer $viewer;

    private readonly TaskDestroyer $destroyer;

    /**
     * @throws ContainerExceptionInterface
     */
    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->viewer = self::get(TaskViewer::class);
        $this->destroyer = self::get(TaskDestroyer::class);
    }

    public static function setUpBeforeClass(): void
    {
        //unlink(Storage::task->value);
        //unlink(Storage::stage->value);
    }

    public function testView(): void
    {
        $flag = new TaskFlag();
        $dto = $this->viewer->get(
            $this->task->getUuid()
        );

        self::assertEquals($this->task->getUuid(), $dto->uuid);
        self::assertEquals($flag->toString(), $dto->state);
    }

    public function testGetOneNotFound(): void
    {
        $this->expectException(NotFoundException::class);

        $this->viewer->get('0000');
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
