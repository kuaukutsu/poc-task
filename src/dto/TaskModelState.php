<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\dto;

use kuaukutsu\poc\task\EntityArrable;
use kuaukutsu\poc\task\state\TaskStateInterface;

/**
 * @readonly
 */
final class TaskModelState implements EntityArrable
{
    public readonly int $flag;

    public readonly string $state;

    public function __construct(TaskStateInterface $state)
    {
        $this->flag = $state->getFlag()->toValue();
        $this->state = serialize($state);
    }

    public function toArray(): array
    {
        return [
            'flag' => $this->flag,
            'state' => $this->state,
        ];
    }
}
