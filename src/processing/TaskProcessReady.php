<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

use kuaukutsu\poc\task\state\TaskCommand;
use RuntimeException;
use SplQueue;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\dto\StageModelState;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\state\TaskStateReady;
use kuaukutsu\poc\task\state\TaskStateRunning;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\EntityUuid;

final class TaskProcessReady
{
    /**
     * @var SplQueue<TaskProcessContext> $queue
     */
    private readonly SplQueue $queue;

    public function __construct(
        private readonly StageQuery $query,
        private readonly StageCommand $command,
    ) {
        $this->queue = new SplQueue();
    }

    public function has(): bool
    {
        return $this->isEmpty() === false;
    }

    public function isEmpty(): bool
    {
        return $this->queue->isEmpty();
    }

    public function count(): int
    {
        return $this->queue->count();
    }

    public function dequeue(): TaskProcessContext
    {
        return $this->queue->dequeue();
    }

    /**
     * @throws RuntimeException Ошибка выполнения комманды
     */
    public function pushStageOnPause(EntityTask $task): bool
    {
        $uuid = new EntityUuid($task->getUuid());
        $stage = $this->query->findPausedByTask($uuid)
            ?? $this->query->findReadyByTask($uuid);

        if ($stage === null) {
            $this->enqueue($task, TaskCommand::stop()->toValue());
            return false;
        }

        return $this->enqueue(
            $task,
            $this->processRun($stage->uuid)->uuid,
            $this->query->findPreviousCompletedByTask($uuid, $stage->order)?->uuid,
        );
    }

    /**
     * @throws RuntimeException Ошибка выполнения комманды
     */
    public function pushStageOnReady(EntityTask $task): bool
    {
        $uuid = new EntityUuid($task->getUuid());
        $stage = $this->query->findReadyByTask($uuid);
        if ($stage === null) {
            $this->enqueue($task, TaskCommand::stop()->toValue());
            return false;
        }

        return $this->enqueue(
            $task,
            $this->processRun($stage->uuid)->uuid,
        );
    }

    /**
     * @throws RuntimeException Ошибка выполнения комманды
     */
    public function pushStageOnForgotten(EntityTask $task): bool
    {
        $uuid = new EntityUuid($task->getUuid());
        $stage = $this->query->findForgottenByTask($uuid);
        if ($stage === null) {
            $this->enqueue($task, TaskCommand::stop()->toValue());
            return false;
        }

        return $this->enqueue(
            $task,
            $this->processRun($stage->uuid)->uuid,
            $this->query->findPreviousCompletedByTask($uuid, $stage->order)?->uuid,
        );
    }

    /**
     * @param non-empty-string $previous
     * @throws RuntimeException Ошибка выполнения комманды
     */
    public function pushStageNext(EntityTask $task, string $previous): bool
    {
        $uuid = new EntityUuid($task->getUuid());
        $stage = $this->query->findPausedByTask($uuid)
            ?? $this->query->findReadyByTask($uuid);

        if ($stage === null) {
            $this->enqueue($task, TaskCommand::stop()->toValue());
            return false;
        }

        return $this->enqueue(
            $task,
            $this->processRun($stage->uuid)->uuid,
            $previous,
        );
    }

    /**
     * @throws RuntimeException Ошибка выполнения комманды
     */
    public function pushStagePromise(EntityTask $task, int $limit): bool
    {
        $index = $this->query->indexReadyByTask(
            new EntityUuid($task->getUuid()),
            $limit,
        );

        if ($index === []) {
            $this->enqueue($task, TaskCommand::stop()->toValue());
            return false;
        }

        foreach ($index as $stageUuid) {
            $this->enqueue($task, $this->processRun($stageUuid)->uuid);
        }

        return true;
    }

    /**
     * @return non-empty-string[] Task UUID
     */
    public function terminate(): array
    {
        $index = [];
        while ($this->queue->isEmpty() === false) {
            $index[$this->queue->dequeue()->task] = true;
        }

        $index = array_keys($index);
        $this->processTerminate($index);

        return $index;
    }

    /**
     * @param non-empty-string $stage
     * @param non-empty-string|null $previous
     */
    private function enqueue(EntityTask $task, string $stage, ?string $previous = null): bool
    {
        $this->queue->enqueue(
            new TaskProcessContext(
                task: $task->getUuid(),
                stage: $stage,
                options: $task->getOptions(),
                previous: $previous,
            )
        );

        return true;
    }

    /**
     * @param non-empty-string $uuid
     * @throws RuntimeException Ошибка выполнения комманды
     */
    private function processRun(string $uuid): StageModel
    {
        return $this->command->state(
            new EntityUuid($uuid),
            new StageModelState(
                new TaskStateRunning(
                    message: new TaskStateMessage('Process is running'),
                )
            ),
        );
    }

    /**
     * @param non-empty-string[] $indexTaskUuid
     */
    private function processTerminate(array $indexTaskUuid): void
    {
        try {
            $this->command->terminateByTask(
                $indexTaskUuid,
                new StageModelState(
                    new TaskStateReady()
                )
            );
        } catch (RuntimeException) {
        }
    }
}
