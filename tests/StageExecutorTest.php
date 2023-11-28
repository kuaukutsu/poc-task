<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use Psr\Container\ContainerExceptionInterface;
use PHPUnit\Framework\TestCase;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\state\TaskStateRunning;
use kuaukutsu\poc\task\handler\StageContextFactory;
use kuaukutsu\poc\task\handler\StageExecutor;
use kuaukutsu\poc\task\EntityWrapper;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\tests\stub\TestResponse;
use kuaukutsu\poc\task\tests\stub\TestStageStub;

use function kuaukutsu\poc\task\tools\entity_hydrator;

final class StageExecutorTest extends TestCase
{
    use Container;

    /**
     * @throws ContainerExceptionInterface
     */
    public function testExecute(): void
    {
        $uuid = new EntityUuid();
        $task = new EntityUuid();
        $state = new TaskStateRunning(
            uuid: $uuid->getUuid(),
            message: new TaskStateMessage('Running'),
        );

        $wrapper = new EntityWrapper(
            class: TestStageStub::class,
            params: [
                'name' => 'executor',
            ]
        );

        $stage = entity_hydrator(
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

        $contextFactory = self::get(StageContextFactory::class);

        $executor = self::get(StageExecutor::class);
        $handlerState = $executor->execute(
            $stage,
            $contextFactory->create($stage),
        );

        self::assertTrue($handlerState->getFlag()->isSuccess());

        $response = $handlerState->getResponse();
        self::assertInstanceOf(TestResponse::class, $response);

        /** @var TestResponse $response */
        self::assertEquals('executor', $response->name);
        self::assertLessThanOrEqual(date('c'), $response->date);
    }
}
