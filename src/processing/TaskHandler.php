<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

use Throwable;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\dto\StageModelState;
use kuaukutsu\poc\task\exception\ProcessingException;
use kuaukutsu\poc\task\handler\StageContextFactory;
use kuaukutsu\poc\task\handler\StageExecutor;
use kuaukutsu\poc\task\handler\TaskFactory;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\service\TaskExecutor;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\state\TaskStateError;
use kuaukutsu\poc\task\state\TaskStateRelation;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\EntityUuid;

final class TaskHandler
{
    public function __construct(
        private readonly TaskQuery $taskQuery,
        private readonly TaskFactory $taskFactory,
        private readonly TaskExecutor $taskExecutor,
        private readonly StageQuery $stageQuery,
        private readonly StageCommand $stageCommand,
        private readonly StageContextFactory $contextFactory,
        private readonly StageExecutor $executor,
    ) {
    }

    /**
     * @param non-empty-string $taskUuid
     * @param non-empty-string $stageUuid
     * @param non-empty-string|null $previousUuid
     * @throws ProcessingException
     */
    public function run(string $taskUuid, string $stageUuid, ?string $previousUuid = null): TaskStateInterface
    {
        $task = $this->getTask($taskUuid);
        if ($task->isFinished()) {
            return $task->getState();
        }

        $state = $this->execute(
            $stageUuid,
            $previousUuid,
        );

        if ($state->getFlag()->isWaiting()) {
            try {
                $this->taskExecutor->wait($task, $state);
            } catch (Throwable $exception) {
                throw new ProcessingException(
                    "[{$task->getUuid()}] TaskProcessing error: " . $exception->getMessage(),
                    $exception,
                );
            }
        }

        $this->stageCommand->state(
            new EntityUuid($stageUuid),
            new StageModelState($state),
        );

        return $state;
    }

    /**
     * @param non-empty-string $taskUuid
     * @throws ProcessingException
     */
    public function complete(string $taskUuid): TaskStateInterface
    {
        $task = $this->getTask($taskUuid);
        if ($task->isFinished()) {
            return $task->getState();
        }

        try {
            $state = $this->taskExecutor->stop($task);
        } catch (Throwable $exception) {
            throw new ProcessingException(
                "[$taskUuid] TaskCompleted error: " . $exception->getMessage(),
                $exception,
            );
        }

        if ($task->isPromised()) {
            $stateRelation = $task->getState();
            if ($stateRelation instanceof TaskStateRelation) {
                $this->promise($stateRelation, $state);
            }

            return $stateRelation;
        }

        return $state;
    }

    /**
     * @param non-empty-string $stageUuid
     * @param non-empty-string|null $previousUuid
     * @throws ProcessingException
     */
    private function execute(string $stageUuid, ?string $previousUuid): TaskStateInterface
    {
        $stage = $this->getStage($stageUuid);

        $previousStage = null;
        if ($previousUuid !== null) {
            $previousStage = $this->getStage($previousUuid);
        }

        try {
            return $this->executor->execute(
                $stage,
                $this->contextFactory->create(
                    $stage,
                    $previousStage,
                )
            );
        } catch (Throwable $exception) {
            return new TaskStateError(
                new TaskStateMessage(
                    $exception->getMessage(),
                    $exception->getTraceAsString(),
                ),
                $stage->flag,
            );
        }
    }

    /**
     * @throws ProcessingException
     */
    private function promise(TaskStateRelation $relation, TaskStateInterface $state): void
    {
        try {
            $this->stageCommand->state(
                new EntityUuid($relation->stage),
                new StageModelState($state),
            );
        } catch (Throwable $exception) {
            throw new ProcessingException(
                "[$relation->task:$relation->stage] TaskPromise error: " . $exception->getMessage(),
                $exception,
            );
        }

        try {
            $this->taskExecutor->run(
                $this->getTask($relation->task)
            );
        } catch (Throwable $exception) {
            throw new ProcessingException(
                "[$relation->task:$relation->stage] TaskRun error: " . $exception->getMessage(),
                $exception,
            );
        }
    }

    /**
     * @param non-empty-string $uuid
     * @throws ProcessingException
     */
    private function getStage(string $uuid): StageModel
    {
        try {
            return $this->stageQuery->getOne(
                new EntityUuid($uuid)
            );
        } catch (Throwable $exception) {
            throw new ProcessingException(
                "[$uuid] Stage error: " . $exception->getMessage(),
                $exception,
            );
        }
    }

    /**
     * @param non-empty-string $uuid
     * @throws ProcessingException
     */
    private function getTask(string $uuid): EntityTask
    {
        try {
            return $this->taskFactory->create(
                $this->taskQuery->getOne(
                    new EntityUuid($uuid)
                )
            );
        } catch (Throwable $exception) {
            throw new ProcessingException(
                "[$uuid] TaskProcessing error: " . $exception->getMessage(),
                $exception,
            );
        }
    }
}
