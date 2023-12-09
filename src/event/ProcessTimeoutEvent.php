<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\event;

use kuaukutsu\poc\task\processing\TaskProcess;
use Symfony\Component\Process\Process;

final class ProcessTimeoutEvent implements EventInterface
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
