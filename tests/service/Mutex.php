<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tests\service;

use RuntimeException;
use SyncMutex;

final class Mutex
{
    public function __construct(
        private readonly SyncMutex $mutex = new SyncMutex('fileStorage')
    ) {
    }

    public function lock(int $second): bool
    {
        if ($this->mutex->lock($second * 1000) === false) {
            throw new RuntimeException('Unable to lock mutex.');
        }

        return true;
    }

    public function unlock(): bool
    {
        return $this->mutex->unlock();
    }
}
