<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use kuaukutsu\poc\task\service\action\ActionCancel;
use kuaukutsu\poc\task\service\action\ActionCompletion;
use kuaukutsu\poc\task\service\action\ActionPause;
use kuaukutsu\poc\task\service\action\ActionResume;
use kuaukutsu\poc\task\service\action\ActionRun;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\EntityTask;

final class TaskExecutor
{
    public function __construct(
        private readonly ActionCancel $actionCancel,
        private readonly ActionCompletion $actionCompletion,
        private readonly ActionPause $actionPause,
        private readonly ActionResume $actionResume,
        private readonly ActionRun $actionRun,
    ) {
    }

    public function run(EntityTask $task): TaskStateInterface
    {
        if ($task->isReady() || $task->isPromised()) {
            return $this->actionRun
                ->execute($task)
                ->getState();
        }

        if ($task->isPaused()) {
            return $this->actionResume
                ->execute($task)
                ->getState();
        }

        return $task->getState();
    }

    public function stop(EntityTask $task): TaskStateInterface
    {
        return $this->actionCompletion
            ->execute($task)
            ->getState();
    }

    public function cancel(EntityTask $task): TaskStateInterface
    {
        return $this->actionCancel
            ->execute($task)
            ->getState();
    }

    public function pause(EntityTask $task): TaskStateInterface
    {
        return $this->actionPause
            ->execute($task)
            ->getState();
    }
}