<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use kuaukutsu\poc\task\service\action\ActionCancel;
use kuaukutsu\poc\task\service\action\ActionCompletion;
use kuaukutsu\poc\task\service\action\ActionPause;
use kuaukutsu\poc\task\service\action\ActionReady;
use kuaukutsu\poc\task\service\action\ActionResume;
use kuaukutsu\poc\task\service\action\ActionRun;
use kuaukutsu\poc\task\service\action\ActionTerminate;
use kuaukutsu\poc\task\service\action\ActionWait;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\EntityTask;

final readonly class TaskExecutor
{
    public function __construct(
        private ActionCancel $actionCancel,
        private ActionCompletion $actionCompletion,
        private ActionPause $actionPause,
        private ActionReady $actionReady,
        private ActionResume $actionResume,
        private ActionRun $actionRun,
        private ActionWait $actionWait,
        private ActionTerminate $actionTerminate,
    ) {
    }

    public function run(EntityTask $task): TaskStateInterface
    {
        if ($task->isReady() || $task->isPromised() || $task->isWaiting()) {
            return $this->actionRun
                ->execute($task)
                ->getState();
        }

        if ($task->isPaused()) {
            return $this->actionResume
                ->execute($task)
                ->getState();
        }

        if ($task->isSkipped()) {
            return $this->actionReady
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

    public function pause(EntityTask $task): TaskStateInterface
    {
        return $this->actionPause
            ->execute($task)
            ->getState();
    }

    public function cancel(EntityTask $task): TaskStateInterface
    {
        return $this->actionCancel
            ->execute($task)
            ->getState();
    }

    public function wait(EntityTask $task, TaskStateInterface $state): TaskStateInterface
    {
        return $this->actionWait
            ->execute($task, $state)
            ->getState();
    }

    /**
     * @param non-empty-string[] $indexTaskUuid
     */
    public function terminate(array $indexTaskUuid, int $signal): void
    {
        if ($indexTaskUuid === []) {
            return;
        }

        $this->actionTerminate->execute($indexTaskUuid, $signal);
    }
}
