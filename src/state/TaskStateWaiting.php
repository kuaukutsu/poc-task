<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use kuaukutsu\poc\task\TaskResponseInterface;

/**
 * @psalm-immutable
 */
final class TaskStateWaiting implements TaskStateInterface
{
    /**
     * @psalm-suppress ImpureMethodCall
     */
    use TaskStateSerialize;

    /**
     * @param non-empty-string $uuid Context::UUID
     * @param positive-int $delay
     */
    public function __construct(
        public readonly string $uuid,
        public readonly int $delay,
        private readonly TaskStateMessage $message,
        private readonly int $flag = 0,
        private readonly ?TaskResponseInterface $response = null,
    ) {
    }

    /**
     * @psalm-suppress ImpureMethodCall
     */
    public function getFlag(): int
    {
        return (new TaskFlag($this->flag))->setWaiting()->toFlag();
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
