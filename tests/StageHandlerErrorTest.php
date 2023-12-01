<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests;

use Psr\Container\ContainerExceptionInterface;
use PHPUnit\Framework\TestCase;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\state\TaskStateReady;
use kuaukutsu\poc\task\handler\StageContextFactory;
use kuaukutsu\poc\task\handler\StageExecutor;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\EntityWrapper;
use kuaukutsu\poc\task\tests\stub\TestCheckResponseStageStub;

use function kuaukutsu\poc\task\tools\entity_hydrator;

final class StageHandlerErrorTest extends TestCase
{
    use Container;

    /**
     * @throws ContainerExceptionInterface
     */
    public function testHandlerFactory(): void
    {
        $stage = $this->generateStage();

        $contextFactory = self::get(StageContextFactory::class);

        $executor = self::get(StageExecutor::class);
        $state = $executor->execute(
            $stage,
            $contextFactory->create($stage),
        );

        self::assertTrue($state->getFlag()->isError());
        self::assertFalse($state->getFlag()->isSuccess());
        self::assertEquals($stage->taskUuid, $state->getMessage()->description);
    }

    private function generateStage(): StageModel
    {
        $uuid = new EntityUuid();
        $task = new EntityUuid();
        $state = new TaskStateReady();
        $wrapper = new EntityWrapper(
            class: TestCheckResponseStageStub::class,
            params: [
                'name' => 'error',
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
}
