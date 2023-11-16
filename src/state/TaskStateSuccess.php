<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use kuaukutsu\poc\task\TaskResponseInterface;

final class TaskStateSuccess implements TaskStateInterface
{
    use TaskStateSerialize;

    /**
     * @param non-empty-string $uuid Context::UUID
     */
    public function __construct(
        public readonly string $uuid,
        private readonly TaskStateMessage $message,
        private readonly ?TaskResponseInterface $response = null,
    ) {
    }

    public function getFlag(): TaskFlag
    {
        return (new TaskFlag())->setSuccess();
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
