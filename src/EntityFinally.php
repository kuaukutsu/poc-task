<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use kuaukutsu\poc\task\state\TaskStateInterface;

interface EntityFinally
{
    public function handle(TaskStateInterface $state): void;
}
