<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\dto;

use kuaukutsu\poc\task\EntityArrable;

/**
 * @readonly
 */
final class StageModel implements EntityArrable
{
    /**
     * @param non-empty-string $uuid
     * @param non-empty-string $taskUuid
     * @param non-empty-string $handler
     * @param non-empty-string $createdAt
     * @param non-empty-string $updatedAt
     */
    public function __construct(
        public readonly string $uuid,
        public readonly string $taskUuid,
        public readonly int $flag,
        public readonly string $state,
        public readonly string $handler,
        public readonly int $order,
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
