<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use kuaukutsu\poc\task\state\TaskStateInterface;

final class TaskStageContext
{
    /**
     * @param non-empty-string $task
     * @param non-empty-string $stage
     */
    public function __construct(
        public readonly string $task,
        public readonly string $stage,
        public readonly ?TaskStateInterface $previous = null,
    ) {
    }
}
