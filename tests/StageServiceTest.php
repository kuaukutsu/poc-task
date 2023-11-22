<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use Psr\Container\ContainerExceptionInterface;
use PHPUnit\Framework\TestCase;
use kuaukutsu\poc\task\dto\StageDto;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\TaskBuilder;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\EntityUuid;

final class StageServiceTest extends TestCase
{
    use Container;
    use TaskFaker;

    private readonly EntityTask $task;

    private readonly StageDto $stage;

    private readonly StageQuery $query;

    private readonly StageCommand $command;

    private readonly TaskCommand $taskCommand;

    /**
     * @throws ContainerExceptionInterface
     */
    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->query = self::get(StageQuery::class);
        $this->command = self::get(StageCommand::class);
        $this->taskCommand = self::get(TaskCommand::class);
    }

    public function testGetOne(): void
    {
        $stage = $this->query->getOne(
            new EntityUuid($this->stage->uuid)
        );

        self::assertEquals($this->stage->uuid, $stage->uuid);
    }

    public function testFindReady(): void
    {
        $stage = $this->query->findReadyByTask(
            new EntityUuid($this->task->getUuid())
        );

        self::assertEquals($this->stage->uuid, $stage->uuid);
    }

    public function testGetOneNotFound(): void
    {
        $this->expectException(NotFoundException::class);

        $this->query->getOne(new EntityUuid());
    }

    public function testFindOneNotFound(): void
    {
        $stage = $this->query->findOne(new EntityUuid());

        self::assertEmpty($stage);
    }

    public static function setUpBeforeClass(): void
    {
        unlink(__DIR__ . '/service/data/task.json');
        unlink(__DIR__ . '/service/data/stage.json');
    }

    /**
     * @throws ContainerExceptionInterface
     */
    protected function setUp(): void
    {
        $this->task = $this->generateTask(
            self::get(TaskBuilder::class)
        );

        $collection = $this->query->getOpenByTask(
            new EntityUuid($this->task->getUuid())
        );

        $this->stage = $collection->getFirst();
    }

    protected function tearDown(): void
    {
        $uuid = new EntityUuid($this->task->getUuid());

        $this->command->removeByTask($uuid);
        $this->taskCommand->remove($uuid);
    }
}
