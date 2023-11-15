<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

use RuntimeException;
use SplQueue;
use kuaukutsu\poc\task\dto\StageDto;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\state\TaskStateReady;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\state\TaskStateRunning;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\service\StageCommand;
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

    public function enqueue(TaskProcessContext $processContext): void
    {
        $this->queue->enqueue($processContext);
    }

    public function terminate(): void
    {
        while ($this->has()) {
            $context = $this->dequeue();
            $this->revertToReady($context);
            // stage to ready
            // task to pause
        }
    }

    /**
     * @param non-empty-string $taskUuid
     */
    public function pushStageOnPause(string $taskUuid): bool
    {
        $stage = $this->query->findPausedByTask(
            new EntityUuid($taskUuid)
        );

        if ($stage === null) {
            return false;
        }

        return $this->enqueueAndRun($stage);
    }

    /**
     * @param non-empty-string $taskUuid
     */
    public function pushStageOnReady(string $taskUuid): bool
    {
        $stage = $this->query->findReadyByTask(
            new EntityUuid($taskUuid)
        );

        if ($stage === null) {
            return false;
        }

        return $this->enqueueAndRun($stage);
    }

    /**
     * @param non-empty-string $taskUuid
     * @return array<string, true>
     */
    public function pushStagePromise(string $taskUuid): array
    {
        $collection = $this->query->getPromiseByTask(
            new EntityUuid($taskUuid)
        );

        if ($collection->isEmpty()) {
            return [];
        }

        $index = [];
        foreach ($collection as $stage) {
            $index[$stage->uuid] = true;
            $this->enqueueAndRun($stage);
        }

        return $index;
    }

    /**
     * @param non-empty-string $taskUuid
     * @param non-empty-string $previous
     */
    public function pushStageNext(string $taskUuid, string $previous): bool
    {
        $uuid = new EntityUuid($taskUuid);
        $stage = $this->query->findPausedByTask($uuid)
            ?? $this->query->findReadyByTask($uuid);

        if ($stage === null) {
            return false;
        }

        return $this->enqueueAndRun($stage, $previous);
    }

    /**
     * @param non-empty-string|null $previous
     */
    private function enqueueAndRun(StageDto $stage, ?string $previous = null): bool
    {
        $state = new TaskStateRunning(
            uuid: $stage->uuid,
            message: new TaskStateMessage('Runned'),
        );

        try {
            $this->command->update(
                new EntityUuid($stage->uuid),
                StageModel::hydrate(
                    [
                        'flag' => $state->getFlag()->toValue(),
                        'state' => serialize($state),
                    ]
                ),
            );
        } catch (RuntimeException) {
            return false;
        }

        $this->queue->enqueue(
            new TaskProcessContext(
                task: $stage->taskUuid,
                stage: $stage->uuid,
                previous: $previous,
            )
        );

        return true;
    }

    private function revertToReady(TaskProcessContext $context): void
    {
        $state = new TaskStateReady();

        try {
            $this->command->update(
                new EntityUuid($context->stage),
                StageModel::hydrate(
                    [
                        'flag' => $state->getFlag()->toValue(),
                        'state' => serialize($state),
                    ]
                ),
            );
        } catch (RuntimeException) {
            return;
        }
    }
}
