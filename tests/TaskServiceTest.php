<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use PHPUnit\Framework\Attributes\Depends;
use Psr\Container\ContainerExceptionInterface;
use PHPUnit\Framework\TestCase;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\handler\StateFactory;
use kuaukutsu\poc\task\handler\TaskFactory;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\TaskBuilder;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\tests\service\Storage;

final class TaskServiceTest extends TestCase
{
    use Container;
    use TaskFaker;

    private readonly EntityTask $task;

    private readonly TaskQuery $query;

    private readonly TaskCommand $command;

    /**
     * @throws ContainerExceptionInterface
     */
    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->query = self::get(TaskQuery::class);
        $this->command = self::get(TaskCommand::class);
    }

    public function testGetOne(): void
    {
        $task = $this->query->getOne(
            new EntityUuid($this->task->getUuid())
        );

        self::assertEquals($this->task->getUuid(), $task->uuid);
    }

    public function testGetOneNotFound(): void
    {
        $this->expectException(NotFoundException::class);

        $this->query->getOne(new EntityUuid());
    }

    public function testGetReady(): void
    {
        $collection = $this->query->getReady(1);

        self::assertCount(1, $collection);
        self::assertEquals($this->task->getUuid(), $collection->getFirst()->uuid);
    }

    public function testGetPausedNotFound(): void
    {
        $collection = $this->query->getPaused(1);

        self::assertEmpty($collection);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    #[Depends('testGetOne')]
    public function testFactory(): void
    {
        $factory = self::get(TaskFactory::class);

        $task = $this->query->getOne(
            new EntityUuid($this->task->getUuid())
        );

        $entity = $factory->create($task);

        self::assertEquals($this->task->getUuid(), $entity->getUuid());
        self::assertTrue($entity->isReady());
        self::assertFalse($entity->isFinished());
    }

    /**
     * @throws ContainerExceptionInterface
     */
    #[Depends('testGetOne')]
    public function testStateFactory(): void
    {
        $stateFactory = self::get(StateFactory::class);

        $task = $this->query->getOne(
            new EntityUuid($this->task->getUuid())
        );

        $state = $stateFactory->create($task->state);

        self::assertTrue($state->getFlag()->isReady());
        self::assertFalse($state->getFlag()->isFinished());
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
        $this->command->remove(
            new EntityUuid($this->task->getUuid())
        );
    }
}
