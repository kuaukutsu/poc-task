<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use kuaukutsu\poc\task\state\TaskFlagCommand;
use kuaukutsu\poc\task\state\TaskStateCommand;
use kuaukutsu\poc\task\state\TaskStateError;

abstract class TaskStageBase implements EntityStage
{
    use TaskFlagCommand;
    use TaskStateCommand;

    public function handleError(TaskStageContext $context, TaskStateError $state): TaskStateError
    {
        return $state;
    }
}
