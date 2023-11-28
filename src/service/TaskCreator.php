<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use Exception;
use kuaukutsu\poc\task\dto\StageModelCreate;
use LogicException;
use Throwable;
use kuaukutsu\poc\task\dto\TaskModelCreate;
use kuaukutsu\poc\task\dto\TaskModel;
use kuaukutsu\poc\task\exception\BuilderException;
use kuaukutsu\poc\task\state\TaskStateReady;
use kuaukutsu\poc\task\state\TaskStateRelation;
use kuaukutsu\poc\task\handler\TaskFactory;
use kuaukutsu\poc\task\EntityWrapperCollection;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\TaskStageContext;
use kuaukutsu\poc\task\TaskDraft;

final class TaskCreator
{
    public function __construct(
        private readonly TaskFactory $factory,
        private readonly TaskQuery $taskQuery,
        private readonly TaskCommand $taskCommand,
        private readonly StageCommand $stageCommand,
        private readonly TaskDestroyer $destroyer,
    ) {
    }

    /**
     * @throws BuilderException
     * @throws LogicException
     */
    public function create(TaskDraft $taskDraft): EntityTask
    {
        if ($this->taskQuery->existsOpenByChecksum($taskDraft->getChecksum())) {
            throw new LogicException(
                "[$taskDraft->title] Task exists."
            );
        }

        $state = new TaskStateReady();
        $model = new TaskModelCreate(
            title: $taskDraft->title,
            flag: $state->getFlag()->toValue(),
            state: serialize($state),
            options: $taskDraft->getOptions(),
            checksum: $taskDraft->getChecksum(),
        );

        try {
            return $this->factory->create(
                $this->save($model, $taskDraft->stages)
            );
        } catch (Throwable $exception) {
            throw new BuilderException("[$taskDraft->title] TaskBuilder failed.", $exception);
        }
    }

    /**
     * @throws BuilderException
     * @throws LogicException
     */
    public function createFromContext(TaskDraft $taskDraft, TaskStageContext $context): EntityTask
    {
        if ($this->taskQuery->existsOpenByChecksum($taskDraft->getChecksum())) {
            throw new LogicException(
                "[$taskDraft->title] Task exists."
            );
        }

        $promise = new TaskStateRelation(
            task: $context->task,
            stage: $context->stage,
        );

        $model = new TaskModelCreate(
            title: $taskDraft->title,
            flag: $promise->getFlag()->toValue(),
            state: serialize($promise),
            options: $taskDraft->getOptions(),
            checksum: $taskDraft->getChecksum(),
        );

        try {
            return $this->factory->create(
                $this->save($model, $taskDraft->stages)
            );
        } catch (Throwable $exception) {
            throw new BuilderException("[$taskDraft->title] TaskBuilder failed.", $exception);
        }
    }

    /**
     * @throws Exception
     * @throws LogicException
     */
    private function save(TaskModelCreate $model, EntityWrapperCollection $stageCollection): TaskModel
    {
        if ($stageCollection->isEmpty()) {
            throw new LogicException(
                "[$model->title] Stage must be declared."
            );
        }

        $uuid = new EntityUuid();
        $task = $this->taskCommand->create($uuid, $model);

        try {
            $order = 0;
            foreach ($stageCollection as $stage) {
                $this->stageCommand->create(
                    new EntityUuid(),
                    new StageModelCreate(
                        taskUuid: $task->uuid,
                        flag: $model->flag,
                        state: serialize($task->state),
                        handler: serialize($stage),
                        order: ++$order,
                    )
                );
            }
        } catch (Exception $exception) {
            $this->destroyer->purge($uuid);
            throw $exception;
        }

        return $task;
    }
}
