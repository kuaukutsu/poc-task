<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use Throwable;
use DI\DependencyException;
use DI\NotFoundException;
use kuaukutsu\poc\task\dto\StageDto;
use kuaukutsu\poc\task\exception\BuilderException;
use kuaukutsu\poc\task\state\TaskStateError;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateWaiting;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\TaskStageContext;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\EntityStage;

final class StageExecutor
{
    public function __construct(
        private readonly TaskQuery $taskQuery,
        private readonly TaskFactory $taskFactory,
        private readonly StateFactory $stateFactory,
        private readonly StageHandlerFactory $handlerFactory,
    ) {
    }

    /**
     * @throws BuilderException
     */
    public function execute(StageDto $stage, TaskStageContext $context): TaskStateInterface
    {
        try {
            /** @psalm-var EntityStage $handler */
            $handler = $this->handlerFactory->create($stage);
        } catch (DependencyException | NotFoundException $exception) {
            throw new BuilderException(
                "[$stage->uuid] StageHandler error: " . $exception->getMessage(),
                $exception,
            );
        }

        $state = $this->stateFactory->create($stage->state);

        try {
            if ($state instanceof TaskStateWaiting) {
                return $handler->handleRelation(
                    $context,
                    $this->factoryTask($state),
                );
            }

            return $handler->handle($context);
        } catch (Throwable $e) {
            return $handler->handleError(
                $context,
                new TaskStateError(
                    uuid: $stage->taskUuid,
                    message: new TaskStateMessage($e->getMessage(), $e->getTraceAsString()),
                    flag: $stage->flag,
                )
            );
        }
    }

    private function factoryTask(TaskStateWaiting $state): EntityTask
    {
        return $this->taskFactory->create(
            $this->taskQuery->getOne(new EntityUuid($state->task))
        );
    }
}
