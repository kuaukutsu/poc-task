<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use SplQueue;
use kuaukutsu\poc\task\dto\StageDto;
use kuaukutsu\poc\task\exception\BuilderException;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\service\TaskQuery;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\TaskProcess;
use kuaukutsu\poc\task\TaskProcessContext;
use kuaukutsu\poc\task\TaskManagerOptions;

final class TaskProcessing
{
    public function __construct(
        private readonly TaskQuery $taskQuery,
        private readonly StageQuery $stageQuery,
        private readonly TaskFactory $taskFactory,
        private readonly ProcessFactory $processFactory,
    ) {
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

    public function loadingPaused(SplQueue $queue, int $limit): void
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
                $task->stop();
                continue;
            }

            $task->run();
            $queue->enqueue(
                new TaskProcessContext(task: $task->getUuid(), stage: $stage->uuid)
            );
        }
    }

    public function loadingReady(SplQueue $queue, int $limit): void
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

            $stage = $this->loadStageReadyOntoQueue($task->getUuid());
            if ($stage === null) {
                $task->stop();
                continue;
            }

            $task->run();
            $queue->enqueue(
                new TaskProcessContext(task: $task->getUuid(), stage: $stage->uuid)
            );
        }
    }

    public function loadingNext(SplQueue $queue, TaskProcess $process): void
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

        if ($task->isFinished() || $task->isPromised()) {
            return;
        }

        if ($process->isSuccessful() === false) {
            $task->stop();
            return;
        }

        $stage = $this->loadStageReadyOntoQueue($task->getUuid());
        if ($stage === null) {
            $task->stop();
            return;
        }

        $queue->enqueue(
            new TaskProcessContext(
                task: $task->getUuid(),
                stage: $stage->uuid,
                previous: $process->stage,
            )
        );
    }

    public function loadingPromise(SplQueue $queue, string $uuid): void
    {
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
