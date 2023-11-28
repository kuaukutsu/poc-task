<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use kuaukutsu\poc\task\dto\TaskOptions;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateReady;

final class TaskDraft
{
    private ?float $timeout = null;

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

    /**
     * @return non-empty-string
     */
    public function getChecksum(): string
    {
        return md5($this->title . $this->stages->getChecksum());
    }

    public function getState(): TaskStateInterface
    {
        return new TaskStateReady();
    }

    public function getOptions(): TaskOptions
    {
        return new TaskOptions(
            timeout: $this->timeout,
        );
    }

    public function setTimeout(float $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }
}
