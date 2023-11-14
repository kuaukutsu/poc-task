<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

use SplQueue;
use kuaukutsu\poc\task\dto\StageDto;
use kuaukutsu\poc\task\exception\BuilderException;
use kuaukutsu\poc\task\exception\StateTransitionException;
use kuaukutsu\poc\task\state\TaskStateRelation;
use kuaukutsu\poc\task\handler\StateFactory;
use kuaukutsu\poc\task\handler\TaskFactory;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\TaskManagerOptions;

/**
 * @fixme: Черновик.
 */
final class TaskProcessing
{
    /**
     * @var SplQueue<TaskProcessContext>
     */
    private readonly SplQueue $stageReady;

    /**
     * @var array<string, TaskRelationContext>
     */
    private array $relationIndex = [];

    public function __construct(
        private readonly TaskQuery $taskQuery,
        private readonly StageQuery $stageQuery,
        private readonly TaskFactory $taskFactory,
        private readonly StateFactory $stateFactory,
        private readonly ProcessFactory $processFactory,
    ) {
        $this->stageReady = new SplQueue();
    }

    public function hasTaskProcess(): bool
    {
        return $this->stageReady->isEmpty() === false;
    }

    public function getTaskProcess(): TaskProcessContext
    {
        return $this->stageReady->dequeue();
    }

    public function loadTaskProcess(TaskManagerOptions $options): void
    {
        // Первым делом в очередь добавляем те что на Паузе
        if ($this->stageReady->isEmpty()) {
            $this->loadingPaused(
                $options->getTaskQueueSize(),
            );
        }

        // Если capacity позволяет, добавляем в очередь задачи из Ожидания
        if ($this->stageReady->count() < $options->getTaskQueueSize()) {
            $this->loadingReady(
                $options->getTaskQueueSize() - $this->stageReady->count(),
            );
        }
    }

    public function start(TaskProcessContext $context, TaskManagerOptions $options): TaskProcess
    {
        $process = $this->processFactory->create($context, $options);
        $process->start();

        return new TaskProcess(
            task: $context->task,
            stage: $context->stage,
            process: $process,
        );
    }

    public function next(TaskProcess $process): void
    {
        try {
            $task = $this->taskFactory->create(
                $this->taskQuery->getOne(
                    new EntityUuid($process->task)
                )
            );
        } catch (BuilderException) {
            return;
        }

        if ($process->isSuccessful() === false) {
            try {
                $task->stop();
            } catch (BuilderException | StateTransitionException) {
            }
            return;
        }

        if ($task->isFinished() || $task->isPromised()) {
            return;
        }

        if (isset($this->relationIndex[$process->task])) {
            $relation = $this->relationIndex[$process->task];
            unset($relation->index[$process->stage]);
            if ($relation->index === []) {
                // записать результат
                // найти связанную задачу и положить в очередь
                $this->stageReady->enqueue(
                    new TaskProcessContext(task: $relation->task, stage: $relation->stage)
                );
                $task->cancel();
            }
        }

        $state = $this->stateFactory->create(
            $process->getOutput()
        );

        if ($state->getFlag()->isWaiting()) {
            return;
        }

        $stage = $this->loadStageReadyOntoQueue($task->getUuid());
        if ($stage === null) {
            try {
                $task->stop();
            } catch (BuilderException | StateTransitionException) {
            }
            return;
        }

        $this->stageReady->enqueue(
            new TaskProcessContext(
                task: $task->getUuid(),
                stage: $stage->uuid,
                previous: $process->stage,
            )
        );
    }

    public function pause(TaskProcess $process): void
    {
        try {
            $task = $this->taskFactory->create(
                $this->taskQuery->getOne(
                    new EntityUuid($process->task)
                )
            );
        } catch (BuilderException) {
            return;
        }

        if ($task->isFinished()) {
            return;
        }

        try {
            $task->pause();
        } catch (BuilderException | StateTransitionException) {
        }
    }

    private function loadingPaused(int $limit): void
    {
        if ($limit < 1) {
            return;
        }

        foreach ($this->taskQuery->getPaused($limit) as $item) {
            try {
                $task = $this->taskFactory->create($item);
            } catch (BuilderException) {
                continue;
            }

            $stage = $this->loadStagePausedOntoQueue($task->getUuid());
            if ($stage === null) {
                try {
                    $task->stop();
                } catch (BuilderException | StateTransitionException) {
                }
                continue;
            }

            try {
                $task->run();
            } catch (BuilderException | StateTransitionException) {
            }

            $this->stageReady->enqueue(
                new TaskProcessContext(task: $task->getUuid(), stage: $stage->uuid)
            );
        }
    }

    private function loadingReady(int $limit): void
    {
        if ($limit < 1) {
            return;
        }

        foreach ($this->taskQuery->getReady($limit) as $item) {
            try {
                $task = $this->taskFactory->create($item);
            } catch (BuilderException) {
                continue;
            }

            if ($task->isPromised()) {
                $collection = $this->stageQuery->getPromiseByTask(new EntityUuid($task->getUuid()));
                if ($collection->isEmpty()) {
                    try {
                        $task->stop();
                    } catch (BuilderException | StateTransitionException) {
                    }
                    continue;
                }

                /** @var TaskStateRelation $state */
                $state = $task->getState();
                $relation = new TaskRelationContext($state->task, $state->stage);

                try {
                    $task->run();
                } catch (BuilderException | StateTransitionException $e) {
                    return;
                }

                foreach ($collection as $stage) {
                    $relation->index[$stage->uuid] = true;
                    $this->stageReady->enqueue(
                        new TaskProcessContext(task: $stage->taskUuid, stage: $stage->uuid)
                    );
                }

                $this->relationIndex[$task->getUuid()] = $relation;

                continue;
            }

            if ($task->isWaiting()) {
                continue;
            }

            $stage = $this->loadStageReadyOntoQueue($task->getUuid());
            if ($stage === null) {
                try {
                    $task->stop();
                } catch (BuilderException | StateTransitionException) {
                }
                continue;
            }

            try {
                $task->run();
            } catch (BuilderException | StateTransitionException) {
            }

            $this->stageReady->enqueue(
                new TaskProcessContext(task: $task->getUuid(), stage: $stage->uuid)
            );
        }
    }

    /**
     * @param non-empty-string $taskUuid
     */
    private function loadStageReadyOntoQueue(string $taskUuid): ?StageDto
    {
        return $this->stageQuery->findReadyByTask(
            new EntityUuid($taskUuid)
        );
    }

    /**
     * @param non-empty-string $taskUuid
     */
    private function loadStagePausedOntoQueue(string $taskUuid): ?StageDto
    {
        return $this->stageQuery->findPausedByTask(
            new EntityUuid($taskUuid)
        );
    }
}
