<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use kuaukutsu\poc\task\service\action\ActionCancel;
use kuaukutsu\poc\task\service\action\ActionResume;
use kuaukutsu\poc\task\service\action\ActionRun;
use kuaukutsu\poc\task\service\action\ActionSkip;
use kuaukutsu\poc\task\service\action\ActionStop;
use kuaukutsu\poc\task\state\TaskFlagCommand;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\state\TaskStatePaused;

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
        private readonly ActionRun $actionRun,
        private readonly ActionResume $actionResume,
        private readonly ActionSkip $actionSkip,
        private readonly ActionStop $actionStop,
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

    public function skip(): TaskStateInterface
    {
        return $this->actionSkip
            ->execute($this)
            ->getState();
    }

    public function cancel(): TaskStateInterface
    {
        return $this->actionCancel
            ->execute($this)
            ->getState();
    }

    public function stop(): TaskStateInterface
    {
        return $this->actionStop
            ->execute($this)
            ->getState();
    }

    public function pause(): TaskStateInterface
    {
        return new TaskStatePaused(
            uuid: $this->uuid,
            message: new TaskStateMessage('Paused'),
        );
    }
}
