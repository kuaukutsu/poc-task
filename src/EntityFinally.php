<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use kuaukutsu\poc\task\state\TaskStateInterface;

interface EntityFinally
{
    /**
     * @param non-empty-string $uuid Task UUID
     */
    public function handle(string $uuid, TaskStateInterface $state): void;
}
