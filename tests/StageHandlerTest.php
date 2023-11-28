<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use Psr\Container\ContainerExceptionInterface;
use PHPUnit\Framework\TestCase;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\state\TaskStateReady;
use kuaukutsu\poc\task\handler\StageContextFactory;
use kuaukutsu\poc\task\handler\StageHandlerFactory;
use kuaukutsu\poc\task\handler\StateFactory;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\EntityWrapper;
use kuaukutsu\poc\task\tests\stub\TestWrapperDto;
use kuaukutsu\poc\task\tests\stub\TestWrapperStageStub;
use kuaukutsu\poc\task\tests\stub\TestStageStub;

use function kuaukutsu\poc\task\tools\entity_hydrator;

final class StageHandlerTest extends TestCase
{
    use Container;

    /**
     * @throws ContainerExceptionInterface
     */
    public function testHandlerFactory(): void
    {
        $stage = $this->generateStage();
        $entityStage = self::get(StageHandlerFactory::class)
            ->create($stage);

        $state = $entityStage->handle(
            self::get(StageContextFactory::class)->create($stage)
        );

        self::assertTrue($state->getFlag()->isSuccess());
        self::assertFalse($state->getFlag()->isError());
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testExtendHandlerFactory(): void
    {
        $stage = $this->generateExtendStage();
        $entityStage = self::get(StageHandlerFactory::class)
            ->create($stage);

        self::assertInstanceOf(TestWrapperStageStub::class, $entityStage);
        /** @var TestWrapperStageStub $entityStage */
        self::assertInstanceOf(TestWrapperDto::class, $entityStage->wrapper);
        self::assertEquals($entityStage->dto->name, $entityStage->wrapper->getName());
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testStateFactory(): void
    {
        $stage = $this->generateStage();
        $state = self::get(StateFactory::class)
            ->create($stage->state);

        self::assertTrue($state->getFlag()->isReady());
        self::assertFalse($state->getFlag()->isFinished());
    }

    private function generateStage(): StageModel
    {
        $uuid = new EntityUuid();
        $task = new EntityUuid();
        $state = new TaskStateReady();
        $wrapper = new EntityWrapper(
            class: TestStageStub::class,
            params: [
                'name' => 'executor',
            ]
        );

        return entity_hydrator(
            StageModel::class,
            [
                'uuid' => $uuid->getUuid(),
                'taskUuid' => $task->getUuid(),
                'flag' => $state->getFlag()->toValue(),
                'handler' => serialize($wrapper),
                'state' => serialize($state),
                'order' => 1,
                'createdAt' => gmdate('c'),
                'updatedAt' => gmdate('c'),
            ]
        );
    }

    private function generateExtendStage(): StageModel
    {
        $uuid = new EntityUuid();
        $task = new EntityUuid();
        $state = new TaskStateReady();

        $test = TestWrapperDto::hydrate(
            [
                'name' => 'test wrapper',
            ]
        );

        $wrapper = new EntityWrapper(
            class: TestWrapperStageStub::class,
            params: [
                'dto' => $test,
                'wrapper' => $test,
            ],
        );

        return entity_hydrator(
            StageModel::class,
            [
                'uuid' => $uuid->getUuid(),
                'taskUuid' => $task->getUuid(),
                'flag' => $state->getFlag()->toValue(),
                'handler' => serialize($wrapper),
                'state' => serialize($state),
                'order' => 1,
                'createdAt' => gmdate('c'),
                'updatedAt' => gmdate('c'),
            ]
        );
    }
}
