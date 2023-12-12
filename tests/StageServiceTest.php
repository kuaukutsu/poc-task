<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use Psr\Container\ContainerExceptionInterface;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\handler\StageContextFactory;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\service\TaskDestroyer;
use kuaukutsu\poc\task\TaskBuilder;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\EntityUuid;

final class StageServiceTest extends TestCase
{
    use Container;
    use TaskFaker;

    private readonly EntityTask $task;

    private readonly StageModel $stage;

    private readonly StageQuery $query;

    private readonly TaskDestroyer $destroyer;

    /**
     * @throws ContainerExceptionInterface
     */
    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->query = self::get(StageQuery::class);
        $this->destroyer = self::get(TaskDestroyer::class);
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
        $generator = $this->query->iterableByTask(
            new EntityUuid()
        );

        self::assertEmpty($generator->current());

        $generator = $this->query->iterableByTask(
            new EntityUuid($this->task->getUuid())
        );

        foreach ($generator as $item) {
            self::assertEquals($this->stage->uuid, $item->uuid);
            break;
        }
    }

    public function testFindByTask(): void
    {
        $generator = $this->query->iterableByTask(
            new EntityUuid()
        );

        self::assertEmpty($generator->current());

        $generator = $this->query->iterableByTask(
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

    public static function setUpBeforeClass(): void
    {
        //unlink(Storage::task->value);
        //unlink(Storage::stage->value);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    protected function setUp(): void
    {
        $this->task = $this->generateTask(
            self::get(TaskBuilder::class)
        );

        $this->stage = $this->query
            ->iterableByTask(
                new EntityUuid($this->task->getUuid())
            )
            ->current();
    }

    protected function tearDown(): void
    {
        $this->destroyer->purge(
            new EntityUuid($this->task->getUuid())
        );
    }
}
