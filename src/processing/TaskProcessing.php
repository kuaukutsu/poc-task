<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

use Throwable;
use kuaukutsu\poc\task\exception\ProcessingException;
use kuaukutsu\poc\task\handler\StateFactory;
use kuaukutsu\poc\task\handler\TaskFactory;
use kuaukutsu\poc\task\state\TaskStateRelation;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\service\TaskExecutor;
use kuaukutsu\poc\task\TaskManagerOptions;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\EntityUuid;

final class TaskProcessing
{
    public function __construct(
        private readonly TaskQuery $taskQuery,
        private readonly TaskFactory $taskFactory,
        private readonly StateFactory $stateFactory,
        private readonly ProcessFactory $processFactory,
        private readonly TaskExecutor $taskExecutor,
        private readonly TaskProcessReady $processReady,
        private readonly TaskProcessPromise $processPromise,
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

    public function terminate(): void
    {
        $this->processReady->terminate();
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

    /**
     * @throws ProcessingException
     */
    public function next(TaskProcess $process): void
    {
        if ($process->isSuccessful() === false) {
            return;
        }

        $task = $this->factory($process->task);
        if ($task->isFinished() || $task->isPromised()) {
            return;
        }

        if ($this->processPromise->has($process->task)) {
            $context = $this->processPromise->dequeue($process->task, $process->stage);
            if ($this->processPromise->canCompleted($context)) {
                $state = $this->taskExecutor->stop($task);
                if (
                    $this->processPromise->completed($context, $state)
                    && $this->processReady->pushStageNext($context->task, $context->stage) === false
                ) {
                    $this->taskExecutor->stop($task);
                }
            }

            return;
        }

        $state = $this->stateFactory->create(
            $process->getOutput()
        );
        if ($state->getFlag()->isWaiting()) {
            return;
        }
        if ($state->getFlag()->isError()) {
            $this->taskExecutor->stop($task);
            return;
        }

        if ($this->processReady->pushStageNext($task->getUuid(), $process->stage) === false) {
            $this->taskExecutor->stop($task);
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
            $this->taskExecutor->cancel($task);
        } catch (Throwable $exception) {
            throw new ProcessingException(
                "[$process->task] TaskProcessing error: " . $exception->getMessage(),
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
                $this->processReady->pushStageOnPause($task->getUuid())
                    ? $this->taskExecutor->run($task)
                    : $this->taskExecutor->stop($task);
            } catch (Throwable $exception) {
                throw new ProcessingException(
                    "[$item->uuid] TaskProcessing error: " . $exception->getMessage(),
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
                $isRun = $task->isPromised()
                    ? $this->enqueuePromise($task)
                    : $this->processReady->pushStageOnReady($task->getUuid());

                $isRun
                    ? $this->taskExecutor->run($task)
                    : $this->taskExecutor->stop($task);
            } catch (Throwable $exception) {
                throw new ProcessingException(
                    "[$item->uuid] TaskProcessing error: " . $exception->getMessage(),
                    $exception,
                );
            }
        }
    }

    private function enqueuePromise(EntityTask $task): bool
    {
        $state = $task->getState();
        if ($state instanceof TaskStateRelation) {
            return $this->processPromise->enqueue(
                $task->getUuid(),
                $this->processReady->pushStagePromise($task->getUuid()),
                $state,
            );
        }

        return false;
    }

    /**
     * @param non-empty-string $uuid
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
