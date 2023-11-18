<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service\action;

use Throwable;
use kuaukutsu\poc\task\dto\StageDto;
use kuaukutsu\poc\task\dto\TaskModel;
use kuaukutsu\poc\task\handler\TaskFactory;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\StageQuery;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\state\TaskStatePaused;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\EntityTask;

final class ActionPause implements TaskAction
{
    use TransitionStateTrait;

    public function __construct(
        private readonly StageQuery $stageQuery,
        private readonly StageCommand $stageCommand,
        private readonly TaskCommand $taskCommand,
        private readonly TaskFactory $factory,
    ) {
    }

    public function execute(EntityTask $task, ?TaskStateInterface $state = null): EntityTask
    {
        $uuid = new EntityUuid($task->getUuid());
        $state ??= new TaskStatePaused(
            uuid: $task->getUuid(),
            message: new TaskStateMessage('Paused'),
            flag: $task->getFlag(),
        );

        $model = $this->taskCommand->update(
            $uuid,
            TaskModel::hydrate(
                [
                    'flag' => $state->getFlag()->toValue(),
                    'state' => serialize($state),
                ]
            ),
        );

        $this->stagePause($uuid);

        return $this->factory->create($model);
    }

    private function stagePause(EntityUuid $uuid): void
    {
        $stageCollection = $this->stageQuery->getOpenByTask($uuid);
        foreach ($stageCollection as $stage) {
            $state = new TaskStatePaused(
                uuid: $stage->uuid,
                message: new TaskStateMessage('Paused'),
                flag: $stage->flag,
            );

            try {
                $this->stageCommand->replace(
                    new EntityUuid($stage->uuid),
                    StageDto::hydrate(
                        [
                            ...$stage->toArray(),
                            'flag' => $state->getFlag()->toValue(),
                            'state' => serialize($state),
                        ]
                    )
                );
            } catch (Throwable) {
            }
        }
    }
}
