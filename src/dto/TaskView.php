<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\dto;

use kuaukutsu\poc\task\EntityArrable;

/**
 * @readonly
 */
final class TaskView implements EntityArrable
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $title,
        public readonly string $state,
        public readonly string $message,
        public readonly TaskMetrics $metrics,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {
    }

    public function toArray(): array
    {
        /** @var array<string, string|array> $attributes */
        $attributes = get_object_vars($this);
        $attributes['metrics'] = $this->metrics->toArray();

        return $attributes;
    }
}
