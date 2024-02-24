<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\dto;

use kuaukutsu\poc\task\EntityArrable;

/**
 * @readonly
 */
final readonly class StageModelCreate implements EntityArrable
{
    public function __construct(
        public string $taskUuid,
        public int $flag,
        public string $state,
        public string $handler,
        public int $order,
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
