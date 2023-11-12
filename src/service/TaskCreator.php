<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use Exception;
use kuaukutsu\poc\task\dto\StageModel;
use kuaukutsu\poc\task\dto\TaskDto;
use kuaukutsu\poc\task\dto\TaskModel;
use kuaukutsu\poc\task\exception\BuilderException;
use kuaukutsu\poc\task\handler\TaskFactory;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\TaskDraft;
use kuaukutsu\poc\task\TaskInterface;
use kuaukutsu\poc\task\TaskStageCollection;

final class TaskCreator
{
    public function __construct(
        private readonly TaskFactory $factory,
        private readonly TaskCommand $taskCommand,
        private readonly StageCommand $stageCommand,
    ) {
    }

    /**
     * @throws Exception
     * @throws BuilderException
     */
    public function create(TaskDraft $taskDraft): TaskInterface
    {
        $dto = $this->save(
            TaskModel::hydrate(
                [
                    'title' => $taskDraft->title,
                    'flag' => $taskDraft->getState()->getFlag()->toValue(),
                    'state' => serialize($taskDraft->getState()),
                    'checksum' => $taskDraft->stages->getChecksum(),
                    'created_at' => gmdate('c'),
                    'updated_at' => gmdate('c'),
                ]
            ),
            $taskDraft->stages
        );

        return $this->factory->create($dto);
    }

    /**
     * @throws Exception
     */
    private function save(TaskModel $model, TaskStageCollection $stageCollection): TaskDto
    {
        $uuid = new EntityUuid();
        $task = $this->taskCommand->create($uuid, $model);

        try {
            $order = 0;
            foreach ($stageCollection as $stage) {
                $this->stageCommand->create(
                    new EntityUuid(),
                    StageModel::hydrate(
                        [
                            'task_uuid' => $task->uuid,
                            'flag' => $model->flag,
                            'handler' => serialize($stage),
                            'state' => '',
                            'order' => ++$order,
                            'created_at' => gmdate('c'),
                            'updated_at' => gmdate('c'),
                        ]
                    ),
                );
            }
        } catch (Exception $exception) {
            $this->stageCommand->removeByTask($uuid);
            $this->taskCommand->remove($uuid);

            throw $exception;
        }

        return $task;
    }
}
