<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use kuaukutsu\poc\task\dto\TaskOptions;
use kuaukutsu\poc\task\state\TaskFlagCommand;
use kuaukutsu\poc\task\state\TaskStateInterface;

use function kuaukutsu\poc\task\tools\entity_hydrator;

final class Task implements EntityTask
{
    use TaskFlagCommand;

    /**
     * @param non-empty-string $uuid
     * @param non-empty-string $title
     * @param array<string, scalar|null> $options
     */
    public function __construct(
        private readonly string $uuid,
        private readonly string $title,
        private readonly TaskStateInterface $state,
        private readonly array $options = [],
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

    public function getOptions(): TaskOptions
    {
        return entity_hydrator(TaskOptions::class, $this->options);
    }
}
