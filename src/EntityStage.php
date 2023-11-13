<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use kuaukutsu\poc\task\state\TaskStateInterface;

/**
 * Дополняет TaskEntityInterface, чем и отличается от Task.
 */
interface EntityStage extends EntityState
{
    public function handle(TaskStageContext $context): TaskStateInterface;
}
