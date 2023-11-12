<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\event;

use DateTimeImmutable;

/**
 * @psalm-immutable
 */
final class LoopTimeoutEvent implements EventInterface
{
    private readonly string $message;

    public function __construct(DateTimeImmutable $time)
    {
        $this->message = 'timeout: ' . $time->format('c');
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
