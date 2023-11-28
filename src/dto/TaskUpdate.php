<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\dto;

use kuaukutsu\poc\task\EntityArrable;

/**
 * @readonly
 */
final class TaskUpdate implements EntityArrable
{
    public function __construct(
        public readonly int $flag,
        public readonly string $state,
    ) {
    }

    public function toArray(): array
    {
        return [
            'flag' => $this->flag,
            'state' => $this->state,
        ];
    }
}
