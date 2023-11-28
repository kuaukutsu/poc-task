<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use kuaukutsu\poc\task\dto\TaskView;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\state\TaskFlag;
use kuaukutsu\poc\task\EntityUuid;

final class TaskViewer
{
    public function __construct(private readonly TaskQuery $query)
    {
    }

    /**
     * @param non-empty-string $uuid
     * @throws NotFoundException
     */
    public function get(string $uuid): TaskView
    {
        $task = $this->query->getOne(
            new EntityUuid($uuid)
        );

        return new TaskView(
            uuid: $task->uuid,
            title: $task->title,
            state: $this->prepareState($task->flag),
            createdAt: $task->createdAt,
            updatedAt: $task->updatedAt,
        );
    }

    /**
     * @return non-empty-string
     */
    private function prepareState(int $flag): string
    {
        return (new TaskFlag($flag))->toString();
    }
}
