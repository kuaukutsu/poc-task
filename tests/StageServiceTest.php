<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use Psr\Container\ContainerExceptionInterface;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use kuaukutsu\poc\task\dto\StageDto;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\handler\StageContextFactory;
use kuaukutsu\poc\task\handler\StageHandlerFactory;
use kuaukutsu\poc\task\handler\StateFactory;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\TaskBuilder;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\tests\service\Storage;

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

    public function testGetOneNotFound(): void
    {
        $this->expectException(NotFoundException::class);

        $this->query->getOne(new EntityUuid());
    }

    public function testFindOne(): void
    {
        $stage = $this->query->findOne(new EntityUuid());

        self::assertEmpty($stage);

        $stage = $this->query->findOne(
            new EntityUuid($this->stage->uuid)
        );

        self::assertEquals($this->stage->uuid, $stage->uuid);
    }

    public function testFindReady(): void
    {
        $stage = $this->query->findReadyByTask(new EntityUuid());

        self::assertEmpty($stage);

        $stage = $this->query->findReadyByTask(
            new EntityUuid($this->task->getUuid())
        );

        self::assertEquals($this->stage->uuid, $stage->uuid);
    }

    public function testGetOpenByTask(): void
    {
        $collection = $this->query->getOpenByTask(
            new EntityUuid()
        );

        self::assertEmpty($collection);

        $collection = $this->query->getOpenByTask(
            new EntityUuid($this->task->getUuid())
        );

        self::assertCount(1, $collection);
        self::assertEquals($this->stage->uuid, $collection->getFirst()->uuid);
    }

    public function testFindByTask(): void
    {
        $generator = $this->query->findByTask(
            new EntityUuid()
        );

        self::assertEmpty($generator->current());

        $generator = $this->query->findByTask(
            new EntityUuid($this->task->getUuid())
        );

        foreach ($generator as $item) {
            self::assertEquals($this->task->getUuid(), $item->taskUuid);
        }
    }

    /**
     * @throws ContainerExceptionInterface
     */
    #[Depends('testGetOne')]
    public function testContextFactory(): void
    {
        $contextFactory = self::get(StageContextFactory::class);

        $stage = $this->query->getOne(
            new EntityUuid($this->stage->uuid)
        );

        $context = $contextFactory->create($stage);

        self::assertEquals($this->task->getUuid(), $context->task);
        self::assertEquals($this->stage->uuid, $context->stage);
        self::assertEmpty($context->previous);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    #[Depends('testContextFactory')]
    public function testHandlerFactory(): void
    {
        $handlerFactory = self::get(StageHandlerFactory::class);
        $contextFactory = self::get(StageContextFactory::class);

        $stage = $this->query->getOne(
            new EntityUuid($this->stage->uuid)
        );

        $entityStage = $handlerFactory->create($stage);
        $state = $entityStage->handle(
            $contextFactory->create($stage)
        );

        self::assertTrue($state->getFlag()->isSuccess());
        self::assertFalse($state->getFlag()->isError());
    }

    /**
     * @throws ContainerExceptionInterface
     */
    #[Depends('testGetOne')]
    public function testStateFactory(): void
    {
        $stateFactory = self::get(StateFactory::class);

        $stage = $this->query->getOne(
            new EntityUuid($this->stage->uuid)
        );

        $state = $stateFactory->create($stage->state);

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
