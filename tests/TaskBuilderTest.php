<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use LogicException;
use Psr\Container\ContainerExceptionInterface;
use PHPUnit\Framework\TestCase;
use kuaukutsu\poc\task\state\TaskStateReady;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\state\TaskStateSkip;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\service\TaskDestroyer;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\EntityWrapper;
use kuaukutsu\poc\task\TaskBuilder;
use kuaukutsu\poc\task\tests\stub\IncreaseNumberStageStub;
use kuaukutsu\poc\task\tests\stub\TestStageStub;
use kuaukutsu\poc\task\tests\stub\TestFinally;
use kuaukutsu\poc\task\tests\stub\TestResponse;

final class TaskBuilderTest extends TestCase
{
    use Container;

    private ?EntityTask $task = null;

    private readonly TaskBuilder $builder;

    private readonly TaskDestroyer $destroyer;

    /**
     * @throws ContainerExceptionInterface
     */
    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->builder = self::get(TaskBuilder::class);
        $this->destroyer = self::get(TaskDestroyer::class);
    }

    public function testDraftBuilder(): void
    {
        $draft = $this->builder->create(
            'task test builder',
            new EntityWrapper(
                class: IncreaseNumberStageStub::class,
                params: [
                    'name' => 'Number initialization.',
                ],
            ),
        );

        self::assertInstanceOf(EntityTask::class, $draft);
        self::assertEquals('task test builder', $draft->getTitle());
        self::assertCount(1, $draft->getStages());

        $draft->addStage(
            new EntityWrapper(
                class: IncreaseNumberStageStub::class,
                params: [
                    'name' => 'Number initialization.',
                ],
            )
        );

        self::assertCount(2, $draft->getStages());
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testCreate(): void
    {
        $draft = $this->builder
            ->create(
                'task test builder',
                new EntityWrapper(
                    class: IncreaseNumberStageStub::class,
                    params: [
                        'name' => 'Number initialization.',
                    ],
                ),
            )
            ->setTimeout(200);

        $task = $this->builder->build($draft);
        self::assertEquals($draft->getTitle(), $task->getTitle());
        self::assertEquals(new TaskStateReady(), $task->getState());
        self::assertEquals($draft->getOptions()->timeout, $task->getOptions()->timeout);

        self::get(TaskDestroyer::class)->purge(
            new EntityUuid($task->getUuid())
        );
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testCreateWithState(): void
    {
        $draft = $this->builder
            ->create(
                'task state builder',
                new EntityWrapper(
                    class: IncreaseNumberStageStub::class,
                    params: [
                        'name' => 'Number initialization.',
                    ],
                ),
            )
            ->setState(
                new TaskStateSkip(
                    new TaskStateMessage('skip')
                )
            );

        $task = $this->builder->build($draft);
        self::assertInstanceOf(TaskStateSkip::class, $task->getState());
        // default
        self::assertEquals(300., $task->getOptions()->timeout);

        self::get(TaskDestroyer::class)->purge(
            new EntityUuid($task->getUuid())
        );
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testCreateChecksum(): void
    {
        $draft = $this->builder->create(
            'checksum',
            new EntityWrapper(
                class: TestStageStub::class,
                params: [
                    'name' => 'Test initialization.',
                ],
            ),
        );

        $draftSimilar = $this->builder->create(
            'checksum',
            new EntityWrapper(
                class: TestStageStub::class,
                params: [
                    'name' => 'Test progressive.',
                ],
            ),
        );

        $draftAnotherSimilar = $this->builder->create(
            'checksum another',
            new EntityWrapper(
                class: TestStageStub::class,
                params: [
                    'name' => 'Test progressive.',
                ],
            ),
        );

        $query = self::get(TaskQuery::class);

        $tasks = [];
        $tasks[] = $this->builder->build($draft);
        self::assertTrue($query->existsOpenByChecksum($draft->getChecksum()));

        $tasks[] = $this->builder->build($draftSimilar);
        self::assertTrue($query->existsOpenByChecksum($draftSimilar->getChecksum()));

        $tasks[] = $this->builder->build($draftAnotherSimilar);
        self::assertTrue($query->existsOpenByChecksum($draftAnotherSimilar->getChecksum()));

        $destroyer = self::get(TaskDestroyer::class);
        foreach ($tasks as $task) {
            $destroyer->purge(
                new EntityUuid($task->getUuid())
            );
        }
    }

    public function testCreateChecksumFailure(): void
    {
        $draft = $this->builder->create(
            'checksum',
            new EntityWrapper(
                class: TestStageStub::class,
                params: [
                    'name' => 'Test initialization.',
                ],
            ),
        );

        $draftDuplicate = $this->builder->create(
            'checksum',
            new EntityWrapper(
                class: TestStageStub::class,
                params: [
                    'name' => 'Test initialization.',
                ],
            ),
        );

        $this->task = $this->builder->build($draft);

        $this->expectException(LogicException::class);
        $this->builder->build($draftDuplicate);
    }

    public function testCreateFinally(): void
    {
        $draft = $this->builder->create(
            'finaly',
            new EntityWrapper(
                class: TestStageStub::class,
                params: [
                    'name' => 'Test initialization.',
                ],
            ),
        );

        $draft->setFinally(TestFinally::class);
        self::assertEquals(TestFinally::class, $draft->getOptions()->finally);
    }

    public function testCreateFinallyException(): void
    {
        $draft = $this->builder->create(
            'finaly',
            new EntityWrapper(
                class: TestStageStub::class,
                params: [
                    'name' => 'Test initialization.',
                ],
            ),
        );

        $this->expectException(LogicException::class);
        $draft->setFinally(TestResponse::class);
    }

    protected function tearDown(): void
    {
        if ($this->task instanceof EntityTask) {
            $this->destroyer->purge(
                new EntityUuid($this->task->getUuid())
            );
        }
    }
}
