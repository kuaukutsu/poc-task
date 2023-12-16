<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

use kuaukutsu\poc\task\dto\TaskOptions;

final class TaskProcessContext
{
    /**
     * @var non-empty-string
     */
    private readonly string $hash;

    /**
     * @param non-empty-string $task Task UUID
     * @param non-empty-string $stage Stage UUID
     * @param non-empty-string|null $previous Stage UUID: Предыдущий этап.
     * @param positive-int|null $timestamp Примерное время (Unix) не раньше которого задача должна быть выполнена.
     */
    public function __construct(
        public readonly string $task,
        public readonly string $stage,
        public readonly TaskOptions $options,
        public readonly ?string $previous = null,
        public readonly ?int $timestamp = null,
    ) {
        $this->hash = hash('crc32b', $this->task . $this->stage);
    }

    /**
     * @return non-empty-string
     */
    public function getHash(): string
    {
        return $this->hash;
    }
}
