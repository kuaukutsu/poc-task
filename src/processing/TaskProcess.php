<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

/**
 * Process Decorator.
 */
final class TaskProcess
{
    /**
     * @see https://tldp.org/LDP/abs/html/exitcodes.html
     */
    public const SUCCESS = 0;
    public const ERROR = 1;

    /**
     * @param non-empty-string $hash
     * @param non-empty-string $task
     * @param non-empty-string $stage
     */
    public function __construct(
        public readonly string $hash,
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
        return $this->prepareOutput($this->process);
    }

    public function getMessage(): string
    {
        return trim($this->getOutput());
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

    public function stop(float $timeout = 10., int $signal = SIGTERM): ?int
    {
        try {
            return $this->process->stop($timeout, $signal);
        } catch (LogicException) {
            // Cannot send signal on a non-running process.
        }

        return null;
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
