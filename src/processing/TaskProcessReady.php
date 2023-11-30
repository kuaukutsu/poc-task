<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

use RuntimeException;
use SplQueue;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\dto\StageModelState;
use kuaukutsu\poc\task\state\TaskStateReady;
use kuaukutsu\poc\task\state\TaskStateMessage;
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
        return $this->queue->isEmpty() === false;
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
        $stage = $this->query->findPausedByTask(new EntityUuid($task->getUuid()));
        if ($stage === null) {
            return false;
        }

        return $this->enqueue($task, $this->processRun($stage->uuid));
    }

    /**
     * @throws RuntimeException Ошибка выполнения комманды
     */
    public function pushStageOnReady(EntityTask $task): bool
    {
        $stage = $this->query->findReadyByTask(new EntityUuid($task->getUuid()));
        if ($stage === null) {
            return false;
        }

        return $this->enqueue($task, $this->processRun($stage->uuid));
    }

    /**
     * @return array<string, true>
     * @throws RuntimeException Ошибка выполнения комманды
     */
    public function pushStagePromise(EntityTask $task): array
    {
        $collection = $this->query->getPromiseByTask(new EntityUuid($task->getUuid()));
        if ($collection->isEmpty()) {
            return [];
        }

        $index = [];
        foreach ($collection as $stage) {
            $index[$stage->uuid] = true;
            $this->enqueue($task, $this->processRun($stage->uuid));
        }

        return $index;
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
            return false;
        }

        return $this->enqueue($task, $this->processRun($stage->uuid), $previous);
    }

    public function terminate(): void
    {
        while ($this->has()) {
            $this->processTerminate(
                $this->dequeue()
            );
        }
    }

    /**
     * @param non-empty-string|null $previous
     */
    private function enqueue(EntityTask $task, StageModel $stage, ?string $previous = null): bool
    {
        $this->queue->enqueue(
            new TaskProcessContext(
                task: $task->getUuid(),
                stage: $stage->uuid,
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
                    uuid: $uuid,
                    message: new TaskStateMessage('Process is running'),
                )
            ),
        );
    }

    private function processTerminate(TaskProcessContext $context): void
    {
        try {
            $this->command->state(
                new EntityUuid($context->stage),
                new StageModelState(new TaskStateReady()),
            );
        } catch (RuntimeException) {
            return;
        }
    }
}
