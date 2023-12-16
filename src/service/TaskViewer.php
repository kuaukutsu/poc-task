<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use Throwable;
use kuaukutsu\poc\task\dto\TaskView;
use kuaukutsu\poc\task\exception\NotFoundException;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateWaiting;
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

        try {
            $state = $this->prepareState($task->state);
        } catch (Throwable) {
            $state = null;
        }

        $relation = null;
        if ($state !== null) {
            $relation = $this->prepareRelation($state);
        }

        return new TaskView(
            uuid: $task->uuid,
            title: $task->title,
            state: $this->prepareFlag($task->flag),
            message: $state?->getMessage()->message ?? 'unrecognized',
            metrics: $this->stageQuery->getMetricsByTask($uuid),
            relation: $relation,
            createdAt: $task->createdAt,
            updatedAt: $task->updatedAt,
        );
    }

    private function prepareRelation(TaskStateInterface $state): ?TaskView
    {
        if ($state instanceof TaskStateWaiting) {
            return $this->get($state->task);
        }

        return null;
    }

    /**
     * @return non-empty-string
     */
    private function prepareFlag(int $flag): string
    {
        return (new TaskFlag($flag))->toString();
    }
}
