<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

use kuaukutsu\poc\task\exception\BuilderException;
use kuaukutsu\poc\task\exception\StateTransitionException;
use kuaukutsu\poc\task\handler\StateFactory;
use kuaukutsu\poc\task\handler\TaskFactory;
use kuaukutsu\poc\task\state\TaskStateRelation;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\TaskManagerOptions;
use kuaukutsu\poc\task\EntityUuid;

/**
 * @fixme: Черновик.
 */
final class TaskProcessing
{
    /**
     * @var array<string, TaskRelationContext>
     */
    private array $relationIndex = [];

    public function __construct(
        private readonly TaskQuery $taskQuery,
        private readonly TaskFactory $taskFactory,
        private readonly StateFactory $stateFactory,
        private readonly ProcessFactory $processFactory,
        private readonly TaskProcessReady $processReady,
    ) {
    }

    public function hasTaskProcess(): bool
    {
        return $this->processReady->has();
    }

    public function getTaskProcess(): TaskProcessContext
    {
        return $this->processReady->dequeue();
    }

    public function loadTaskProcess(TaskManagerOptions $options): void
    {
        // Первым делом в очередь добавляем те что на Паузе
        if ($this->processReady->isEmpty()) {
            $this->loadingPaused(
                $options->getTaskQueueSize(),
            );
        }

        // Если capacity позволяет, добавляем в очередь задачи из Ожидания
        $capacity = $options->getTaskQueueSize() - $this->processReady->count();
        if ($capacity > 0) {
            $this->loadingReady($capacity);
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
                $this->processReady->enqueue(
                    new TaskProcessContext(task: $relation->task, stage: $relation->stage)
                );
                $task->cancel();
            }

            return;
        }

        $state = $this->stateFactory->create(
            $process->getOutput()
        );

        if ($state->getFlag()->isWaiting()) {
            return;
        }

        try {
            if ($this->processReady->pushStageNext($task->getUuid(), $process->stage) === false) {
                $task->stop();
            }
        } catch (BuilderException | StateTransitionException) {
        }
    }

    public function terminate(): void
    {
        $this->processReady->terminate();
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

    /**
     * @param positive-int $limit
     */
    private function loadingPaused(int $limit): void
    {
        foreach ($this->taskQuery->getPaused($limit) as $item) {
            try {
                $task = $this->taskFactory->create($item);
            } catch (BuilderException) {
                continue;
            }

            try {
                $this->processReady->pushStageOnPause($task->getUuid())
                    ? $task->run()
                    : $task->stop();
            } catch (BuilderException | StateTransitionException) {
                continue;
            }
        }
    }

    /**
     * @param positive-int $limit
     */
    private function loadingReady(int $limit): void
    {
        foreach ($this->taskQuery->getReady($limit) as $item) {
            try {
                $task = $this->taskFactory->create($item);
            } catch (BuilderException) {
                continue;
            }

            if ($task->isPromised()) {
                $index = $this->processReady->pushStagePromise($task->getUuid());
                if ($index === []) {
                    try {
                        $task->stop();
                    } catch (BuilderException | StateTransitionException) {
                    }
                    continue;
                }

                /** @var TaskStateRelation $state */
                $state = $task->getState();
                $relation = new TaskRelationContext(
                    $state->task,
                    $state->stage,
                    $index
                );

                try {
                    $task->run();
                } catch (BuilderException | StateTransitionException) {
                    return;
                }

                $this->relationIndex[$task->getUuid()] = $relation;

                continue;
            }

            try {
                $this->processReady->pushStageOnReady($task->getUuid())
                    ? $task->run()
                    : $task->stop();
            } catch (BuilderException | StateTransitionException) {
                continue;
            }
        }
    }
}
