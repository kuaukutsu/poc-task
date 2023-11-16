<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\event;

use kuaukutsu\poc\task\exception\ProcessingException;

/**
 * @psalm-immutable
 */
final class LoopExceptionEvent implements EventInterface
{
    private readonly string $message;

    public function __construct(ProcessingException $exception)
    {
        $this->message = 'error: ' . $exception->getMessage();
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
