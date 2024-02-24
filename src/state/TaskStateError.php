<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use kuaukutsu\poc\task\TaskResponseInterface;

final readonly class TaskStateError implements TaskStateInterface
{
    use TaskStateSerialize;

    public function __construct(
        private TaskStateMessage $message,
        private int $flag = 0,
        private ?TaskResponseInterface $response = null,
    ) {
    }

    public function getFlag(): TaskFlag
    {
        return (new TaskFlag($this->flag))->setError();
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
