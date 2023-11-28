<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\dto;

use kuaukutsu\poc\task\EntityArrable;

/**
 * @readonly
 */
final class TaskView implements EntityArrable
{
    /**
     * @param non-empty-string $uuid
     * @param non-empty-string $title
     * @param non-empty-string $state
     * @param non-empty-string $createdAt
     * @param non-empty-string $updatedAt
     */
    public function __construct(
        public readonly string $uuid,
        public readonly string $title,
        public readonly string $state,
        public readonly string $createdAt,
        public readonly string $updatedAt,
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
