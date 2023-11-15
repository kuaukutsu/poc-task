<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

final class TaskManagerOptions
{
    /**
     * @param float $heartbeat in Seconds
     * @param float $keeperInterval in Seconds
     * @param float|null $timeout Event Loop Timeout in Second
     * @param int[] $interruptSignals A POSIX signal
     */
    public function __construct(
        private readonly ?string $bindir = null,
        private readonly float $heartbeat = 30.,
        private readonly float $keeperInterval = 5.,
        private readonly int $taskQueueSize = 10,
        public readonly ?float $timeout = null,
        public readonly array $interruptSignals = [SIGHUP, SIGINT, SIGTERM],
    ) {
    }

    public function getBindir(): string
    {
        return $this->bindir
            ?? dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bin';
    }

    public function getHeartbeat(): float
    {
        return max(1., $this->heartbeat);
    }

    public function getKeeperInterval(): float
    {
        return max(1., $this->keeperInterval);
    }

    /**
     * @return positive-int
     */
    public function getTaskQueueSize(): int
    {
        return max(1, $this->taskQueueSize);
    }
}
