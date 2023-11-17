<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use kuaukutsu\poc\task\TaskResponseInterface;

final class TaskStateRunning implements TaskStateInterface
{
    use TaskStateSerialize;

    /**
     * @param non-empty-string $uuid Context::UUID
     */
    public function __construct(
        public readonly string $uuid,
        private readonly TaskStateMessage $message,
        private readonly int $flag = 0,
    ) {
    }

    public function getFlag(): TaskFlag
    {
        return (new TaskFlag($this->flag))->setRunning();
    }

    public function getMessage(): TaskStateMessage
    {
        return $this->message;
    }

    public function getResponse(): ?TaskResponseInterface
    {
        return null;
    }
}
