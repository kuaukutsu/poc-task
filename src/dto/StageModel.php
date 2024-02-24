<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\dto;

use kuaukutsu\poc\task\EntityArrable;

/**
 * @readonly
 */
final readonly class StageModel implements EntityArrable
{
    /**
     * @param non-empty-string $uuid
     * @param non-empty-string $taskUuid
     * @param non-empty-string $handler
     * @param non-empty-string $createdAt
     * @param non-empty-string $updatedAt
     */
    public function __construct(
        public string $uuid,
        public string $taskUuid,
        public int $flag,
        public string $state,
        public string $handler,
        public int $order,
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
