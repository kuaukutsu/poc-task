<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use kuaukutsu\poc\task\TaskResponseInterface;

final class TaskStateWaiting implements TaskStateInterface
{
    use TaskStateSerialize;

    /**
     * @param non-empty-string $uuid Context::UUID
     */
    public function __construct(
        public readonly string $uuid,
        public readonly string $task,
        private readonly TaskStateMessage $message,
        private readonly int $flag = 0,
        private readonly ?TaskResponseInterface $response = null,
    ) {
    }

    public function getFlag(): TaskFlag
    {
        return (new TaskFlag($this->flag))->setWaiting();
    }

    public function getMessage(): TaskStateMessage
    {
        return $this->message;
    }

    public function getResponse(): ?TaskResponseInterface
    {
        return $this->response;
    }
}
