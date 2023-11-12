<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use kuaukutsu\poc\task\state\TaskFlagCommand;
use kuaukutsu\poc\task\state\TaskStateCommand;

abstract class TaskStageBase implements TaskStageInterface
{
    use TaskFlagCommand;
    use TaskStateCommand;
    use TaskStageSerialize;
}
