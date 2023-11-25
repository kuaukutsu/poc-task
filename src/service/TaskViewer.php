<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use kuaukutsu\poc\task\dto\TaskViewDto;
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
    public function get(string $uuid): TaskViewDto
    {
        $task = $this->query->getOne(
            new EntityUuid($uuid)
        );

        return TaskViewDto::hydrate(
            [
                ...$task->toArrayRecursive(),
                'state' => $this->prepareState($task->flag),
            ]
        );
    }

    private function prepareState(int $flag): string
    {
        return (new TaskFlag($flag))->toString();
    }
}
