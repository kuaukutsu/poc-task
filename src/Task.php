<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use kuaukutsu\poc\task\service\action\ActionCancel;
use kuaukutsu\poc\task\service\action\ActionPause;
use kuaukutsu\poc\task\service\action\ActionResume;
use kuaukutsu\poc\task\service\action\ActionRun;
use kuaukutsu\poc\task\service\action\ActionCompletion;
use kuaukutsu\poc\task\state\TaskFlagCommand;
use kuaukutsu\poc\task\state\TaskStateInterface;

final class Task implements EntityTask
{
    use TaskFlagCommand;

    /**
     * @param non-empty-string $uuid
     * @param non-empty-string $title
     */
    public function __construct(
        private readonly string $uuid,
        private readonly string $title,
        private readonly TaskStateInterface $state,
        private readonly ActionCancel $actionCancel,
        private readonly ActionCompletion $actionCompletion,
        private readonly ActionPause $actionPause,
        private readonly ActionResume $actionResume,
        private readonly ActionRun $actionRun,
    ) {
        $this->flag = $this->state->getFlag();
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getState(): TaskStateInterface
    {
        return $this->state;
    }

    public function run(): TaskStateInterface
    {
        if ($this->isReady() || $this->isPromised()) {
            return $this->actionRun
                ->execute($this)
                ->getState();
        }

        if ($this->isPaused()) {
            return $this->actionResume
                ->execute($this)
                ->getState();
        }

        return $this->getState();
    }

    public function stop(): TaskStateInterface
    {
        return $this->actionCompletion
            ->execute($this)
            ->getState();
    }

    public function cancel(): TaskStateInterface
    {
        return $this->actionCancel
            ->execute($this)
            ->getState();
    }

    public function pause(): TaskStateInterface
    {
        return $this->actionPause
            ->execute($this)
            ->getState();
    }
}
