<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\event;

use kuaukutsu\poc\task\processing\TaskProcessContext;

final class ProcessContextEvent implements EventInterface
{
    private readonly string $uuid;

    private readonly string $message;

    public function __construct(TaskProcessContext $context)
    {
        $this->uuid = $context->task;

        $time = date('c', $context->timestamp);
        $this->message = "[{$context->getHash()}] timer $time: " . $context->task;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
