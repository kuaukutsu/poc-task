<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use kuaukutsu\poc\task\dto\TaskView;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\state\TaskFlag;
use kuaukutsu\poc\task\state\TaskStatePrepare;
use kuaukutsu\poc\task\EntityUuid;

final class TaskViewer
{
    use TaskStatePrepare;

    public function __construct(
        private readonly TaskQuery $taskQuery,
        private readonly StageQuery $stageQuery,
    ) {
    }

    /**
     * @param non-empty-string $taskUuid
     * @throws NotFoundException
     */
    public function get(string $taskUuid): TaskView
    {
        $uuid = new EntityUuid($taskUuid);
        $task = $this->taskQuery->getOne($uuid);
        $state = $this->prepareState($task->state);

        return new TaskView(
            uuid: $task->uuid,
            title: $task->title,
            state: $this->prepareFlag($task->flag),
            message: $state->getMessage()->message,
            metrics: $this->stageQuery->getMetricsByTask($uuid),
            createdAt: $task->createdAt,
            updatedAt: $task->updatedAt,
        );
    }

    /**
     * @return non-empty-string
     */
    private function prepareFlag(int $flag): string
    {
        return (new TaskFlag($flag))->toString();
    }
}
