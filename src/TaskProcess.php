<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

/**
 * Process Decorator.
 */
final class TaskProcess
{
    private ?string $output = null;

    /**
     * @param non-empty-string $task
     * @param non-empty-string $stage
     */
    public function __construct(
        public readonly string $task,
        public readonly string $stage,
        private readonly Process $process,
    ) {
    }

    public function getStatus(): string
    {
        return $this->process->getStatus();
    }

    public function getOutput(): string
    {
        if ($this->output === null) {
            $this->output = $this->prepareOutput($this->process);
        }

        return $this->output;
    }

    public function isSuccessful(): bool
    {
        return $this->process->isSuccessful();
    }

    public function isStarted(): bool
    {
        return $this->process->isStarted();
    }

    public function isRunning(): bool
    {
        return $this->process->isRunning();
    }

    public function start(): void
    {
        $this->process->start();
    }

    public function stop(float $timeout = 10., int $signal = SIGTERM): int
    {
        try {
            return $this->process->stop($timeout, $signal) ?? 0;
        } catch (LogicException) {
            // Cannot send signal on a non-running process.
        }

        return 0;
    }

    /**
     * @throws ProcessTimedOutException
     */
    public function checkTimeout(): void
    {
        $this->process->checkTimeout();
    }

    private function prepareOutput(Process $process): string
    {
        if ($process->isSuccessful()) {
            return $process->getOutput();
        }

        $output = $process->getOutput();
        if ($output === '') {
            $output = $process->getErrorOutput();
        }

        return $output;
    }
}
