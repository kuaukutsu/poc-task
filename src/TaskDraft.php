<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateReady;

final class TaskDraft
{
    public function __construct(
        public readonly string $title,
        public readonly TaskStageCollection $stages = new TaskStageCollection(),
    ) {
    }

    public function addStage(TaskStageInterface ...$stages): self
    {
        foreach ($stages as $stage) {
            if ($this->stages->contains($stage) === false) {
                $this->stages->attach($stage);
            }
        }

        return $this;
    }

    public function getState(): TaskStateInterface
    {
        return new TaskStateReady();
    }
}
