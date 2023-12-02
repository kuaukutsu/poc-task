<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use Exception;
use LogicException;
use kuaukutsu\poc\task\dto\StageModelCreate;
use kuaukutsu\poc\task\dto\TaskModelCreate;
use kuaukutsu\poc\task\dto\TaskModel;
use kuaukutsu\poc\task\exception\BuilderException;
use kuaukutsu\poc\task\state\TaskStateRelation;
use kuaukutsu\poc\task\handler\TaskFactory;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\TaskStageContext;
use kuaukutsu\poc\task\TaskDraft;

final class TaskCreator
{
    public function __construct(
        private readonly TaskQuery $taskQuery,
        private readonly TaskCommand $taskCommand,
        private readonly StageCommand $stageCommand,
        private readonly TaskFactory $factory,
        private readonly TaskDestroyer $destroyer,
    ) {
    }

    /**
     * @throws BuilderException
     * @throws LogicException
     */
    public function create(TaskDraft $draft): EntityTask
    {
        $this->validateDraft($draft);

        return $this->factory->create(
            $this->save($draft)
        );
    }

    /**
     * @throws BuilderException
     * @throws LogicException
     */
    public function createFromContext(TaskDraft $draft, TaskStageContext $context): EntityTask
    {
        $this->validateDraft($draft);

        $draft->setState(
            new TaskStateRelation(
                task: $context->task,
                stage: $context->stage,
            )
        );

        return $this->factory->create(
            $this->save($draft)
        );
    }

    /**
     * @throws BuilderException
     */
    private function save(TaskDraft $draft): TaskModel
    {
        $task = $this->taskCommand->create(
            new EntityUuid($draft->getUuid()),
            new TaskModelCreate(
                title: $draft->getTitle(),
                flag: $draft->getFlag(),
                state: serialize($draft->getState()),
                options: $draft->getOptions(),
                checksum: $draft->getChecksum(),
            )
        );

        try {
            $order = 0;
            foreach ($draft->getStages() as $stage) {
                $this->stageCommand->create(
                    new EntityUuid(),
                    new StageModelCreate(
                        taskUuid: $task->uuid,
                        flag: $task->flag,
                        state: serialize($task->state),
                        handler: serialize($stage),
                        order: ++$order,
                    )
                );
            }
        } catch (Exception $exception) {
            $this->destroyer->purge(
                new EntityUuid($task->uuid)
            );

            throw new BuilderException("[{$draft->getTitle()}] TaskBuilder failed.", $exception);
        }

        return $task;
    }

    /**
     * @throws LogicException
     */
    private function validateDraft(TaskDraft $draft): void
    {
        if ($this->taskQuery->existsOpenByChecksum($draft->getChecksum())) {
            throw new LogicException(
                "[{$draft->getTitle()}] Task exists."
            );
        }

        if ($draft->getStages()->isEmpty()) {
            throw new LogicException(
                "[{$draft->getTitle()}] Stage must be declared."
            );
        }
    }
}
