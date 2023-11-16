<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

use RuntimeException;
use SplQueue;
use kuaukutsu\poc\task\dto\StageDto;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\exception\NotFoundException;
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

    /**
     * @param non-empty-string $taskUuid
     * @throws RuntimeException Ошибка выполнения комманды
     */
    public function pushStageOnPause(string $taskUuid): bool
    {
        $stage = $this->query->findPausedByTask(
            new EntityUuid($taskUuid)
        );

        if ($stage === null) {
            return false;
        }

        return $this->enqueue(
            $this->stageToRun($stage->uuid)
        );
    }

    /**
     * @param non-empty-string $taskUuid
     * @throws RuntimeException Ошибка выполнения комманды
     */
    public function pushStageOnReady(string $taskUuid): bool
    {
        $stage = $this->query->findReadyByTask(
            new EntityUuid($taskUuid)
        );

        if ($stage === null) {
            return false;
        }

        return $this->enqueue(
            $this->stageToRun($stage->uuid)
        );
    }

    /**
     * @param non-empty-string $taskUuid
     * @return array<string, true>
     * @throws RuntimeException Ошибка выполнения комманды
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
            $this->enqueue(
                $this->stageToRun($stage->uuid)
            );
        }

        return $index;
    }

    /**
     * @throws NotFoundException
     */
    public function pushStageWaiting(TaskProcessContext $processContext): bool
    {
        $stage = $this->query->getOne(new EntityUuid($processContext->stage));
        if ($stage->taskUuid !== $processContext->task) {
            return false;
        }

        $this->enqueue($stage);

        return true;
    }

    /**
     * @param non-empty-string $taskUuid
     * @param non-empty-string $previous
     * @throws RuntimeException Ошибка выполнения комманды
     */
    public function pushStageNext(string $taskUuid, string $previous): bool
    {
        $uuid = new EntityUuid($taskUuid);
        $stage = $this->query->findPausedByTask($uuid)
            ?? $this->query->findReadyByTask($uuid);

        if ($stage === null) {
            return false;
        }

        return $this->enqueue(
            $this->stageToRun($stage->uuid),
            $previous,
        );
    }

    public function terminate(): void
    {
        while ($this->has()) {
            $context = $this->dequeue();
            $this->processTerminate($context);
            // stage to ready
            // task to pause
        }
    }

    /**
     * @param non-empty-string|null $previous
     */
    private function enqueue(StageDto $stage, ?string $previous = null): bool
    {
        $this->queue->enqueue(
            new TaskProcessContext(
                task: $stage->taskUuid,
                stage: $stage->uuid,
                previous: $previous,
            )
        );

        return true;
    }

    /**
     * @param non-empty-string $uuid
     * @throws RuntimeException Ошибка выполнения комманды
     */
    private function stageToRun(string $uuid): StageDto
    {
        $state = new TaskStateRunning(
            uuid: $uuid,
            message: new TaskStateMessage('Runned'),
        );

        return $this->command->update(
            new EntityUuid($uuid),
            StageModel::hydrate(
                [
                    'flag' => $state->getFlag()->toValue(),
                    'state' => serialize($state),
                ]
            ),
        );
    }

    private function processTerminate(TaskProcessContext $context): void
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
