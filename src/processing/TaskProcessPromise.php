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

    public function canCompleted(TaskProcessContext $context): bool
    {
        return $context->previous !== null
            && array_key_exists($context->previous, $this->queue)
            && $this->queue[$context->previous]->storage === [];
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
    public function completed(TaskProcessContext $context, TaskStateInterface $statePrevious): TaskStateInterface
    {
        $stage = $this->query->getOne(
            new EntityUuid($context->stage)
        );

        if ($statePrevious->getFlag()->isSuccess()) {
            $state = new TaskStateSuccess(
                message: $statePrevious->getMessage(),
                response: $statePrevious->getResponse(),
            );
        } elseif ($statePrevious->getFlag()->isError()) {
            $state = new TaskStateError(
                message: $statePrevious->getMessage(),
                flag: $stage->flag,
                response: $statePrevious->getResponse(),
            );
        } elseif ($statePrevious->getFlag()->isError()) {
            $state = new TaskStateCanceled(
                message: $statePrevious->getMessage(),
                flag: $stage->flag,
            );
        } else {
            $state = new TaskStateSkip(
                message: $statePrevious->getMessage(),
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
