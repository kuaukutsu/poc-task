<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

final readonly class TaskManagerOptions
{
    /**
     * @param float $heartbeat in Seconds
     * @param float $keeperInterval in Seconds
     * @param float|null $timeout Event Loop Timeout in Second
     * @param int[] $interruptSignals A POSIX signal
     */
    public function __construct(
        private ?string $bindir = null,
        private float $heartbeat = 30.,
        private float $keeperInterval = 5.,
        private int $queueSize = 10,
        public ?float $timeout = null,
        public string $handler = 'handler.php',
        public array $interruptSignals = [SIGHUP, SIGINT, SIGTERM],
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
    public function getQueueSize(): int
    {
        return max(1, $this->queueSize);
    }
}
