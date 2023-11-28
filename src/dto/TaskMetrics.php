<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\dto;

use kuaukutsu\poc\task\EntityArrable;

final class TaskMetrics implements EntityArrable
{
    public function __construct(
        public readonly int $count = 0,
        public readonly int $running = 0,
        public readonly int $waiting = 0,
        public readonly int $success = 0,
        public readonly int $canceled = 0,
        public readonly int $failed = 0,
    ) {
    }

    public function toArray(): array
    {
        /**
         * @var array<string, int>
         */
        return get_object_vars($this);
    }
}
