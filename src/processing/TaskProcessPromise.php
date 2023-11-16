<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

use kuaukutsu\poc\task\state\TaskStateRelation;

final class TaskProcessPromise
{
    /**
     * @var array<string, TaskProcessContext>
     */
    private array $queue = [];

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

    public function canCloseProcess(TaskProcessContext $context): bool
    {
        return $context->previous !== null
            && array_key_exists($context->previous, $this->queue)
            && $this->queue[$context->previous]->storage === [];
    }
}
