<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateReady;

final class TaskDraft
{
    /**
     * @param non-empty-string $title
     */
    public function __construct(
        public readonly string $title,
        public readonly EntityWrapperCollection $stages = new EntityWrapperCollection(),
    ) {
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

    public function getState(): TaskStateInterface
    {
        return new TaskStateReady();
    }
}
