<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\dto;

use kuaukutsu\poc\task\EntityArrable;

final readonly class TaskMetrics implements EntityArrable
{
    public function __construct(
        public int $count = 0,
        public int $running = 0,
        public int $waiting = 0,
        public int $success = 0,
        public int $canceled = 0,
        public int $failed = 0,
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
