<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use kuaukutsu\poc\task\state\TaskStateInterface;

final readonly class TaskStageContext
{
    /**
     * @param non-empty-string $task
     * @param non-empty-string $stage
     */
    public function __construct(
        public string $task,
        public string $stage,
        public ?TaskStateInterface $previous = null,
    ) {
    }
}
