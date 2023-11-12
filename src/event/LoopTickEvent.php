<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\event;

use DateTimeImmutable;

/**
 * @psalm-immutable
 */
final class LoopTickEvent implements EventInterface
{
    private readonly string $message;

    public function __construct(DateTimeImmutable $tick)
    {
        $this->message = 'tick: ' . $tick->format('c');
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
