<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

use kuaukutsu\poc\task\TaskResponseInterface;

final class TaskResponseContext implements TaskResponseInterface
{
    /**
     * @param array<non-empty-string, TaskResponseInterface> $success
     * @param array<non-empty-string, TaskResponseInterface> $failure
     */
    public function __construct(
        public readonly array $success = [],
        public readonly array $failure = [],
    ) {
    }
}
