<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\state;

use kuaukutsu\poc\task\TaskResponseInterface;

final readonly class TaskStateDelay implements TaskStateInterface
{
    use TaskStateSerialize;

    public const DELAY_OPEN_STAGE = 5;
    public const DELAY_PROMISE = 5;
    public const DELAY_MAX_SECOND = 300;

    /**
     * @param non-empty-string $uuid Stage[Command] UUID
     * @param positive-int $delay Second
     */
    public function __construct(
        public string $uuid,
        public int $delay,
        public int $flag,
    ) {
    }

    public function getFlag(): TaskFlag
    {
        return new TaskFlag($this->flag);
    }

    public function getMessage(): TaskStateMessage
    {
        return new TaskStateMessage("Delay $this->delay second.");
    }

    public function getResponse(): ?TaskResponseInterface
    {
        return null;
    }
}
