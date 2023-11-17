<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use kuaukutsu\poc\task\TaskResponseInterface;

final class TaskStateRelation implements TaskStateInterface
{
    use TaskStateSerialize;

    /**
     * @param non-empty-string $task TaskRelation::UUID
     * @param non-empty-string $stage StageRelation::UUID
     */
    public function __construct(
        public readonly string $task,
        public readonly string $stage,
        private readonly int $flag = 0,
    ) {
    }

    public function getFlag(): TaskFlag
    {
        return (new TaskFlag($this->flag))->setPromised();
    }

    public function getMessage(): TaskStateMessage
    {
        return new TaskStateMessage("[$this->task] $this->stage relation.");
    }

    public function getResponse(): ?TaskResponseInterface
    {
        return null;
    }
}
