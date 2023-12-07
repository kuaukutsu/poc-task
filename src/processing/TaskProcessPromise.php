<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\dto\StageModelState;
use kuaukutsu\poc\task\exception\ProcessingException;
use kuaukutsu\poc\task\state\TaskStateCanceled;
use kuaukutsu\poc\task\state\TaskStateError;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateRelation;
use kuaukutsu\poc\task\state\TaskStateSkip;
use kuaukutsu\poc\task\state\TaskStateSuccess;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\EntityUuid;
use Throwable;

final class TaskProcessPromise
{
    /**
     * @var array<string, TaskProcessContext>
     */
    private array $queue = [];

    public function __construct(
        private readonly StageQuery $query,
        private readonly StageCommand $command,
    ) {
    }

    /**
     * @param non-empty-string $uuid
     */
    public function has(string $uuid): bool
    {
        return array_key_exists($uuid, $this->queue)
            && $this->queue[$uuid]->storage !== [];
    }

    /**
     * @param non-empty-string $uuid
     */
    public function canCompleted(string $uuid): bool
    {
        return array_key_exists($uuid, $this->queue)
            && $this->queue[$uuid]->storage === [];
    }

    /**
     * @param non-empty-array<string, true> $index
     */
    public function enqueue(EntityTask $task, TaskStateRelation $state, array $index): bool
    {
        $this->queue[$task->getUuid()] = new TaskProcessContext(
            $state->task,
            $state->stage,
            $task->getOptions(),
            $task->getUuid(),
            $index,
        );

        return true;
    }

    /**
     * @throws ProcessingException
     */
    public function dequeue(string $uuid, string $stage): TaskProcessContext
    {
        if (array_key_exists($uuid, $this->queue)) {
            unset($this->queue[$uuid]->storage[$stage]);
            return $this->queue[$uuid];
        }

        throw new ProcessingException("[$uuid] Queue promise is empty.");
    }

    /**
     * @throws ProcessingException
     */
    public function completed(TaskProcessContext $context, TaskStateInterface $taskState): TaskStateInterface
    {
        $stage = $this->query->getOne(
            new EntityUuid($context->stage)
        );

        if ($taskState->getFlag()->isSuccess()) {
            $state = new TaskStateSuccess(
                message: $taskState->getMessage(),
                response: $taskState->getResponse(),
            );
        } elseif ($taskState->getFlag()->isError()) {
            $state = new TaskStateError(
                message: $taskState->getMessage(),
                flag: $stage->flag,
                response: $taskState->getResponse(),
            );
        } elseif ($taskState->getFlag()->isError()) {
            $state = new TaskStateCanceled(
                message: $taskState->getMessage(),
                flag: $stage->flag,
            );
        } else {
            $state = new TaskStateSkip(
                message: $taskState->getMessage(),
            );
        }

        $this->state($stage, $state);
        return $state;
    }

    /**
     * @throws ProcessingException Ошибка выполнения комманды
     */
    private function state(StageModel $stage, TaskStateInterface $state): void
    {
        try {
            $this->command->state(
                new EntityUuid($stage->uuid),
                new StageModelState($state),
            );
        } catch (Throwable $exception) {
            throw new ProcessingException(
                "[$stage->taskUuid] TaskProcessing, [$stage->uuid] Stage error: " . $exception->getMessage(),
                $exception,
            );
        }
    }
}
