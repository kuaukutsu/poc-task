<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\handler;

use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStatePrepare;

final class StateFactory
{
    use TaskStatePrepare;

    public function create(string $state): TaskStateInterface
    {
        return $this->prepareState($state);
    }
}
