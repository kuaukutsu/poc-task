<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

use Throwable;
use kuaukutsu\poc\task\exception\ProcessingException;
use kuaukutsu\poc\task\handler\StateFactory;
use kuaukutsu\poc\task\handler\TaskFactory;
use kuaukutsu\poc\task\state\TaskStateInterface;
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
        private readonly TaskExecutor $taskExecutor,
        private readonly TaskProcessReady $processReady,
        private readonly TaskProcessPromise $processPromise,
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
        if ($process->isSuccessful() === false) {
            return;
        }

        $task = $this->factory($process->task);
        if ($task->isFinished()) {
            return;
        }

        if ($this->processPromise->has($process->task)) {
            $context = $this->processPromise->getContextIfCompleted($process->task, $process->stage);
            if ($context !== null) {
                $state = $this->processPromise->completed(
                    $context->stage,
                    $this->taskExecutor->stop($task)
                );

                $this->enqueueNext(
                    $this->factory($context->task),
                    $state,
                    $context->stage,
                );
            }

            return;
        }

        $state = $this->stateFactory->create(
            $task->getUuid(),
            $process->getOutput(),
        );

        $this->enqueueNext($task, $state, $process->stage);
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

        if ($this->processPromise->has($process->task)) {
            $context = $this->processPromise->dequeue($process->task, $process->stage);

            try {
                $this->taskExecutor->cancel(
                    $this->factory($context->task)
                );
            } catch (Throwable $exception) {
                throw new ProcessingException(
                    "[$process->task] TaskProcessing error: " . $exception->getMessage(),
                    $exception,
                );
            }
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
                $this->processReady->pushStageOnPause($task)
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
                    : $this->processReady->pushStageOnReady($task);

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

    /**
     * @param positive-int $limit
     * @throws ProcessingException
     */
    private function loadingForgotten(int $limit): void
    {
        foreach ($this->taskQuery->getRunning($limit) as $item) {
            try {
                $task = $this->taskFactory->create($item);
                $this->processReady->pushStageOnRunning($task)
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
     * @param non-empty-string $previousStage
     * @throws ProcessingException
     */
    private function enqueueNext(EntityTask $task, TaskStateInterface $state, string $previousStage): void
    {
        if ($state->getFlag()->isWaiting()) {
            $this->taskExecutor->wait($task, $state);
            return;
        }

        if ($state->getFlag()->isFinished() === false) {
            return;
        }

        try {
            if ($this->processReady->pushStageNext($task, $previousStage) === false) {
                $this->taskExecutor->stop($task);
                return;
            }

            if ($task->isWaiting()) {
                $this->taskExecutor->run($task);
            }
        } catch (Throwable $exception) {
            throw new ProcessingException(
                "[{$task->getUuid()}] TaskProcessing error: " . $exception->getMessage(),
                $exception,
            );
        }
    }

    private function enqueuePromise(EntityTask $task): bool
    {
        $state = $task->getState();
        if ($state instanceof TaskStateRelation) {
            $index = $this->processReady->pushStagePromise($task);
            if ($index === []) {
                return false;
            }

            return $this->processPromise->enqueue($task, $state, $index);
        }

        return false;
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
