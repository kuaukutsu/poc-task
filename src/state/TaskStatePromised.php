<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use kuaukutsu\poc\task\TaskResponseInterface;

final class TaskStatePromised implements TaskStateInterface
{
    use TaskStateSerialize;

    /**
     * @param non-empty-string $uuid Context::UUID
     * @param class-string<> Обработчик обещания.
     */
    public function __construct(
        public readonly string $uuid,
        private readonly string $handler,
        private readonly int $flag = 0,
        private readonly ?TaskResponseInterface $response = null,
    ) {
    }

    public function getFlag(): TaskFlag
    {
        return (new TaskFlag($this->flag))->setPromised();
    }

    public function getMessage(): TaskStateMessage
    {
        return new TaskStateMessage($this->handler);
    }

    public function getResponse(): ?TaskResponseInterface
    {
        return $this->response;
    }
}
