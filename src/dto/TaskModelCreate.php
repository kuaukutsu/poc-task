<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\dto;

use kuaukutsu\poc\task\EntityArrable;

/**
 * @readonly
 */
final class TaskModelCreate implements EntityArrable
{
    /**
     * @param non-empty-string $title
     * @param non-empty-string $checksum
     */
    public function __construct(
        public readonly string $title,
        public readonly int $flag,
        public readonly string $state,
        public TaskOptions $options,
        public readonly string $checksum,
    ) {
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'flag' => $this->flag,
            'state' => $this->state,
            'options' => $this->options->toArray(),
            'checksum' => $this->checksum,
        ];
    }
}
