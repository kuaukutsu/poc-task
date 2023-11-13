<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use kuaukutsu\poc\task\state\TaskStateInterface;

interface EntityStage extends EntityState
{
    public function handle(TaskStageContext $context): TaskStateInterface;
}
