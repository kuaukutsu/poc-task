<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\event;

use kuaukutsu\poc\task\TaskProcess;

final class StageEvent implements EventInterface
{
    private readonly string $message;

    public function __construct(
        private readonly TaskProcess $process,
    ) {
        $this->message = "[{$this->process->task}] " . $this->process->stage;
    }

    public function getUuid(): string
    {
        return $this->process->stage;
    }

    public function getStatus(): string
    {
        return $this->process->getStatus();
    }

    public function getOutput(): string
    {
        return $this->process->getOutput();
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
