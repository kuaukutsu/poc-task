<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use kuaukutsu\poc\task\service\action\ActionResume;
use kuaukutsu\poc\task\service\action\ActionRun;
use kuaukutsu\poc\task\service\action\ActionStop;
use kuaukutsu\poc\task\state\TaskFlag;
use kuaukutsu\poc\task\state\TaskFlagCommand;
use kuaukutsu\poc\task\state\TaskStateCanceled;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\state\TaskStatePaused;
use kuaukutsu\poc\task\state\TaskStateSkip;

final class Task implements TaskInterface
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
        private readonly ActionRun $actionRun,
        private readonly ActionResume $actionResume,
        private readonly ActionStop $actionStop,
    ) {
        $this->flag = new TaskFlag($this->state->getFlag());
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
        if ($this->isReady()) {
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
        return new TaskStateSkip(
            uuid: $this->uuid,
            message: new TaskStateMessage('Skiped'),
        );
    }

    public function cancel(): TaskStateInterface
    {
        return new TaskStateCanceled(
            uuid: $this->uuid,
            message: new TaskStateMessage('Canceled'),
        );
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
