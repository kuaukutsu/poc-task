<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\event;

/**
 * @readonly
 * @psalm-immutable
 */
final class LoopTickEvent implements EventInterface
{
    private readonly string $message;

    public function __construct(int $countProcessActive, int $countProcessDelay)
    {
        $datetime = gmdate('c');
        $this->message = "tick: $datetime, active: $countProcessActive, delay: $countProcessDelay.";
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
