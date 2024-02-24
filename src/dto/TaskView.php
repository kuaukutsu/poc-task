<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\dto;

use kuaukutsu\poc\task\EntityArrable;

/**
 * @readonly
 */
final readonly class TaskView implements EntityArrable
{
    public function __construct(
        public string $uuid,
        public string $title,
        public string $state,
        public string $message,
        public TaskMetrics $metrics,
        public ?TaskView $relation,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public function toArray(): array
    {
        /** @var array<string, string|array> $attributes */
        $attributes = get_object_vars($this);
        $attributes['metrics'] = $this->metrics->toArray();

        if ($this->relation instanceof TaskView) {
            $attributes['relation'] = $this->relation->toArray();
        }

        return $attributes;
    }
}
