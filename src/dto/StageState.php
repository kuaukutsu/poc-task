<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\dto;

use kuaukutsu\poc\task\EntityArrable;
use kuaukutsu\poc\task\state\TaskStateInterface;

/**
 * @readonly
 */
final class StageState implements EntityArrable
{
    private readonly int $flag;

    private readonly string $state;

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
