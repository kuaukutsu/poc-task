<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

final class TaskRelationContext
{
    /**
     * @param non-empty-string $task Task UUID
     * @param non-empty-string $stage Stage UUID
     * @param array $index
     */
    public function __construct(
        public readonly string $task,
        public readonly string $stage,
        public array $index = [],
    ) {
    }
}
