<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\event;

use Symfony\Component\Process\Process;
use kuaukutsu\poc\task\TaskProcess;

final class StageTimeoutEvent implements EventInterface
{
    public function __construct(
        private readonly TaskProcess $process,
        private readonly string $message,
    ) {
    }

    public function getUuid(): string
    {
        return $this->process->stage;
    }

    public function getStatus(): string
    {
        return Process::STATUS_TERMINATED;
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
