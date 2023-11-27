<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

use kuaukutsu\poc\task\dto\TaskOptions;

final class TaskProcessContext
{
    /**
     * @param non-empty-string $task Task UUID
     * @param non-empty-string $stage Stage UUID
     * @param non-empty-string|null $previous Stage UUID: Предыдущий этап.
     * @param array<string, scalar> $storage Для передачи данных между этапами задачи.
     */
    public function __construct(
        public readonly string $task,
        public readonly string $stage,
        public readonly TaskOptions $options,
        public readonly ?string $previous = null,
        public array $storage = [],
    ) {
    }
}
