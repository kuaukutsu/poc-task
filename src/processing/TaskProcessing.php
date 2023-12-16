<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

use Throwable;
use kuaukutsu\poc\task\exception\ProcessingException;
use kuaukutsu\poc\task\handler\StateFactory;
use kuaukutsu\poc\task\handler\TaskFactory;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\service\TaskExecutor;
use kuaukutsu\poc\task\state\TaskStateDelay;
use kuaukutsu\poc\task\state\TaskStateRelation;
use kuaukutsu\poc\task\TaskManagerOptions;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\EntityUuid;

final class TaskProcessing
{
    public function __construct(
        private readonly TaskQuery $taskQuery,
        private readonly TaskFactory $taskFactory,
        private readonly TaskExecutor $taskExecutor,
        private readonly TaskProcessReady $processReady,
        private readonly StateFactory $stateFactory,
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

    /**
     * @throws ProcessingException
     */
    public function loadTaskProcess(TaskManagerOptions $options): void
    {
        // Первым делом в очередь добавляем те что на Паузе.
        if ($this->processReady->isEmpty()) {
            $this->loadingPaused(
                $options->getQueueSize(),
            );
        }

        // Если capacity позволяет, добавляем в очередь задачи из Ожидания.
        $capacity = $options->getQueueSize() - $this->processReady->count();
        if ($capacity > 0) {
            $this->loadingReady($capacity);
            $this->loadingPromise(
                $options->getQueueSize()
            );
        }
    }

    /**
     * @throws ProcessingException
     */
    public function checkTaskProcess(TaskManagerOptions $options): void
    {
        if ($this->processReady->isEmpty()) {
            $this->loadingForgotten(
                $options->getQueueSize()
            );
        }
    }

    public function terminate(int $signal): void
    {
        $this->taskExecutor->terminate(
            $this->processReady->terminate(),
            $signal,
        );
    }

    /**
     * @throws ProcessingException
     */
    public function next(TaskProcess $process): void
    {
        $task = $this->factory($process->task);
        if ($task->isPromised()) {
            return;
        }

        $state = $this->stateFactory->create(
            $process->task,
            $process->getOutput(),
        );

        if ($task->isFinished()) {
            if ($state instanceof TaskStateRelation) {
                $this->nextStage(
                    $this->factory($state->task),
                    $state->stage,
                );
            }

            return;
        }

        if ($state instanceof TaskStateDelay) {
            $this->processReady->pushStageOnDelay($task, $state);
            return;
        }

        if ($state->getFlag()->isFinished()) {
            $this->nextStage($task, $process->stage);
        }
    }

    /**
     * @throws ProcessingException
     */
    public function cancel(TaskProcess $process): void
    {
        $task = $this->factory($process->task);
        if ($task->isFinished()) {
            return;
        }

        try {
            $this->taskExecutor->cancel(
                $task,
                $this->stateFactory->create(
                    $process->task,
                    $process->getMessage(),
                )
            );
        } catch (Throwable $exception) {
            throw new ProcessingException(
                "[$process->task] TaskCanceled error: " . $exception->getMessage(),
                $exception,
            );
        }
    }

    /**
     * @throws ProcessingException
     */
    public function pause(TaskProcess $process): void
    {
        $task = $this->factory($process->task);
        if ($task->isFinished()) {
            return;
        }

        try {
            $this->taskExecutor->pause($task);
        } catch (Throwable $exception) {
            throw new ProcessingException(
                "[$process->task] TaskProcessing error: " . $exception->getMessage(),
                $exception,
            );
        }
    }

    /**
     * @param positive-int $limit
     * @throws ProcessingException
     */
    private function loadingPaused(int $limit): void
    {
        foreach ($this->taskQuery->getPaused($limit) as $item) {
            try {
                $task = $this->taskFactory->create($item);
                if ($this->processReady->pushStageOnPause($task)) {
                    $this->taskExecutor->run($task);
                }
            } catch (Throwable $exception) {
                throw new ProcessingException(
                    "[$item->uuid] TaskLoading error: " . $exception->getMessage(),
                    $exception,
                );
            }
        }
    }

    /**
     * @param positive-int $limit
     * @throws ProcessingException
     */
    private function loadingReady(int $limit): void
    {
        foreach ($this->taskQuery->getReady($limit) as $item) {
            try {
                $task = $this->taskFactory->create($item);
                if ($this->processReady->pushStageOnReady($task)) {
                    $this->taskExecutor->run($task);
                }
            } catch (Throwable $exception) {
                throw new ProcessingException(
                    "[$item->uuid] TaskLoading error: " . $exception->getMessage(),
                    $exception,
                );
            }
        }
    }

    /**
     * @param positive-int $limit
     * @throws ProcessingException
     */
    private function loadingPromise(int $limit): void
    {
        foreach ($this->taskQuery->getPromise($limit) as $item) {
            try {
                $this->processReady->pushStagePromise(
                    $this->taskFactory->create($item),
                    $limit,
                );
            } catch (Throwable $exception) {
                throw new ProcessingException(
                    "[$item->uuid] TaskLoading error: " . $exception->getMessage(),
                    $exception,
                );
            }
        }
    }

    /**
     * @param positive-int $limit
     * @throws ProcessingException
     */
    private function loadingForgotten(int $limit): void
    {
        foreach ($this->taskQuery->getForgotten($limit) as $item) {
            try {
                $this->processReady->pushStageOnForgotten(
                    $this->taskFactory->create($item)
                );
            } catch (Throwable $exception) {
                throw new ProcessingException(
                    "[$item->uuid] TaskLoading error: " . $exception->getMessage(),
                    $exception,
                );
            }
        }
    }

    /**
     * @param non-empty-string $previousStage
     * @throws ProcessingException
     */
    private function nextStage(EntityTask $task, string $previousStage): void
    {
        try {
            $this->processReady->pushStageNext($task, $previousStage);
        } catch (Throwable $exception) {
            throw new ProcessingException(
                "[{$task->getUuid()}] TaskProcessing error: " . $exception->getMessage(),
                $exception,
            );
        }
    }

    /**
     * @param non-empty-string $uuid
     * @throws ProcessingException
     */
    private function factory(string $uuid): EntityTask
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
