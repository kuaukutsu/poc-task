<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\dto;

use kuaukutsu\poc\task\EntityArrable;

/**
 * @readonly
 */
final readonly class TaskModel implements EntityArrable
{
    /**
     * @param non-empty-string $uuid
     * @param non-empty-string $title
     * @param array<string, scalar> $options
     * @param non-empty-string $checksum
     * @param non-empty-string $createdAt
     * @param non-empty-string $updatedAt
     */
    public function __construct(
        public string $uuid,
        public string $title,
        public int $flag,
        public string $state,
        public array $options,
        public string $checksum,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public function toArray(): array
    {
        /**
         * @var array<string, scalar|array>
         */
        return get_object_vars($this);
    }
}
