<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use kuaukutsu\poc\task\state\TaskStateInterface;

interface EntityTask extends EntityState
{
    /**
     * @return non-empty-string
     */
    public function getUuid(): string;

    /**
     * @return non-empty-string
     */
    public function getTitle(): string;

    public function getState(): TaskStateInterface;
}
