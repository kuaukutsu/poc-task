<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

use RuntimeException;
use kuaukutsu\poc\task\dto\StageDto;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\state\TaskStateError;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateRelation;
use kuaukutsu\poc\task\state\TaskStateSuccess;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\EntityUuid;

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
     * @param non-empty-string $uuid
     * @param array<string, true> $index
     */
    public function enqueue(string $uuid, array $index, TaskStateRelation $state): bool
    {
        if ($index === []) {
            return false;
        }

        $this->queue[$uuid] = new TaskProcessContext(
            $state->task,
            $state->stage,
            $uuid,
            $index,
        );

        return true;
    }

    public function dequeue(string $uuid, string $stage): ?TaskProcessContext
    {
        if (array_key_exists($uuid, $this->queue)) {
            unset($this->queue[$uuid]->storage[$stage]);
            return $this->queue[$uuid];
        }

        return null;
    }

    public function completed(TaskProcessContext $context, TaskStateInterface $statePrevious): bool
    {
        $stage = $this->query->getOne(new EntityUuid($context->stage));
        if ($stage->taskUuid !== $context->task) {
            return false;
        }

        if ($statePrevious->getFlag()->isSuccess()) {
            $this->stageSuccess($stage, $statePrevious);
            return true;
        }

        $this->stageError($stage, $statePrevious);
        return false;
    }

    /**
     * @throws RuntimeException Ошибка выполнения комманды
     */
    private function stageError(StageDto $stage, TaskStateInterface $statePrevious): void
    {
        $state = new TaskStateError(
            uuid: $stage->uuid,
            message: $statePrevious->getMessage(),
            flag: $stage->flag,
            response: $statePrevious->getResponse(),
        );

        $this->command->update(
            new EntityUuid($stage->uuid),
            StageModel::hydrate(
                [
                    'flag' => $state->getFlag()->toValue(),
                    'state' => serialize($state),
                ]
            ),
        );
    }

    /**
     * @throws RuntimeException Ошибка выполнения комманды
     */
    private function stageSuccess(StageDto $stage, TaskStateInterface $statePrevious): void
    {
        $state = new TaskStateSuccess(
            uuid: $stage->uuid,
            message: $statePrevious->getMessage(),
            response: $statePrevious->getResponse(),
        );

        $this->command->update(
            new EntityUuid($stage->uuid),
            StageModel::hydrate(
                [
                    'flag' => $state->getFlag()->toValue(),
                    'state' => serialize($state),
                ]
            ),
        );
    }
}
