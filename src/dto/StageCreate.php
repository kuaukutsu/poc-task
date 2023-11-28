<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\dto;

use kuaukutsu\poc\task\EntityArrable;

/**
 * @readonly
 */
final class StageCreate implements EntityArrable
{
    public function __construct(
        public readonly string $taskUuid,
        public readonly int $flag,
        public readonly string $state,
        public readonly string $handler,
        public readonly int $order,
    ) {
    }

    public function toArray(): array
    {
        return [
            'task_uuid' => $this->taskUuid,
            'flag' => $this->flag,
            'state' => $this->state,
            'handler' => $this->handler,
            'order' => $this->order,
        ];
    }
}
