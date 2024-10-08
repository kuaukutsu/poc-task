<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task;

use LogicException;
use kuaukutsu\poc\task\dto\TaskOptions;
use kuaukutsu\poc\task\state\TaskFlagCommand;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateReady;

final class TaskDraft implements EntityTask
{
    use TaskFlagCommand;

    private float $timeout = 300.;

    /**
     * @var class-string<EntityFinally>|null
     */
    private ?string $finally = null;

    /**
     * @var array<string, scalar>
     */
    private array $params = [];

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
            finally: $this->finally,
            params: $this->params,
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
        $this->timeout = max(1, $timeout);
        return $this;
    }

    /**
     * @param class-string<EntityFinally> $handler
     * @param array<string, scalar> $params Params for implementating EntityFinally
     * @throws LogicException not implement the EntityFinally
     */
    public function setFinally(string $handler, array $params = []): self
    {
        if (is_a($handler, EntityFinally::class, true) === false) {
            throw new LogicException("[$handler] must implement the EntityFinally.");
        }

        $this->finally = $handler;
        $this->params = $params;
        return $this;
    }
}
