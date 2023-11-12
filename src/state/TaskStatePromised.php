<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use kuaukutsu\poc\task\TaskResponseInterface;

/**
 * @psalm-immutable
 */
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

    /**
     * @psalm-suppress ImpureMethodCall
     */
    public function getFlag(): int
    {
        return (new TaskFlag($this->flag))->setPromised()->toFlag();
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
