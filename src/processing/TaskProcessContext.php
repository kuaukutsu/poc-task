<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\processing;

use kuaukutsu\poc\task\dto\TaskOptions;

final readonly class TaskProcessContext
{
    /**
     * @var non-empty-string
     */
    private string $hash;

    /**
     * @param non-empty-string $task Task UUID
     * @param non-empty-string $stage Stage UUID
     * @param non-empty-string|null $previous Stage UUID: Предыдущий этап.
     * @param positive-int|null $timestamp Примерное время (Unix) не раньше которого задача должна быть выполнена.
     */
    public function __construct(
        public string $task,
        public string $stage,
        public TaskOptions $options,
        public ?string $previous = null,
        public ?int $timestamp = null,
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
