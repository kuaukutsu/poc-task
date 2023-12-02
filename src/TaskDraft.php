<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use kuaukutsu\poc\task\dto\TaskOptions;
use kuaukutsu\poc\task\state\TaskFlagCommand;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateReady;

final class TaskDraft implements EntityTask
{
    use TaskFlagCommand;

    private ?float $timeout = null;

    private TaskStateInterface $state;

    private readonly EntityUuid $uuid;

    /**
     * @param non-empty-string $title
     */
    public function __construct(
        private readonly string $title,
        private readonly EntityWrapperCollection $stages = new EntityWrapperCollection(),
    ) {
        $this->uuid = new EntityUuid();
        $this->setState(new TaskStateReady());
    }

    public function getUuid(): string
    {
        return $this->uuid->getUuid();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getState(): TaskStateInterface
    {
        return $this->state;
    }

    public function getOptions(): TaskOptions
    {
        return new TaskOptions(
            timeout: $this->timeout,
        );
    }

    /**
     * @return non-empty-string
     */
    public function getChecksum(): string
    {
        return md5($this->title . $this->stages->getChecksum());
    }

    public function getStages(): EntityWrapperCollection
    {
        return $this->stages;
    }

    public function addStage(EntityWrapper ...$stages): self
    {
        foreach ($stages as $stage) {
            if ($this->stages->contains($stage) === false) {
                $this->stages->attach($stage);
            }
        }

        return $this;
    }

    public function setState(TaskStateInterface $state): self
    {
        $this->state = $state;
        $this->flag = $state->getFlag();
        return $this;
    }

    public function setTimeout(float $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }
}
