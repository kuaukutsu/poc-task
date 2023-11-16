<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service\action;

use kuaukutsu\poc\task\exception\BuilderException;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\exception\StateTransitionException;
use kuaukutsu\poc\task\state\TaskStateInterface;

interface TaskAction
{
    /**
     * @throws BuilderException
     * @throws StateTransitionException Если переход не возможен.
     */
    public function execute(EntityTask $task, ?TaskStateInterface $state = null): EntityTask;
}
