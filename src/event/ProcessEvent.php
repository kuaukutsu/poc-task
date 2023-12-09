<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\event;

use kuaukutsu\poc\task\processing\TaskProcess;

final class ProcessEvent implements EventInterface
{
    private readonly string $uuid;

    private readonly string $status;

    private readonly string $output;

    private readonly string $message;

    public function __construct(TaskProcess $process)
    {
        $this->uuid = $process->stage;
        $this->status = $process->getStatus();
        $this->output = $process->getOutput();
        $this->message = "[$process->task] " . $process->stage;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getOutput(): string
    {
        return $this->output;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
