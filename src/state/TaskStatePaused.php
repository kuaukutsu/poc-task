<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use kuaukutsu\poc\task\TaskResponseInterface;

final class TaskStatePaused implements TaskStateInterface
{
    use TaskStateSerialize;

    public function __construct(
        private readonly TaskStateMessage $message,
        private readonly int $flag = 0,
    ) {
    }

    public function getFlag(): TaskFlag
    {
        return (new TaskFlag($this->flag))->setPaused();
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
