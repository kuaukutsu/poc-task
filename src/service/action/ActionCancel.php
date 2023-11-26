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
use kuaukutsu\poc\task\state\TaskStateCanceled;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\EntityTask;

final class ActionCancel implements TaskAction
{
    public function __construct(
        private readonly StageQuery $stageQuery,
        private readonly StageCommand $stageCommand,
        private readonly TaskCommand $taskCommand,
        private readonly TaskFactory $factory,
        private readonly TransitionState $transition,
    ) {
    }

    public function execute(EntityTask $task, ?TaskStateInterface $state = null): EntityTask
    {
        $uuid = new EntityUuid($task->getUuid());
        $state ??= new TaskStateCanceled(
            uuid: $task->getUuid(),
            message: new TaskStateMessage('Canceled'),
            flag: $task->getFlag(),
        );

        $this->transition->canAccessTransitionState(
            $task->getUuid(),
            $task->getFlag(),
            $state->getFlag()->toValue(),
        );

        $model = $this->taskCommand->update(
            new EntityUuid($task->getUuid()),
            TaskModel::hydrate(
                [
                    'flag' => $state->getFlag()->toValue(),
                    'state' => serialize($state),
                ]
            ),
        );

        $this->stageCancel($uuid);

        return $this->factory->create($model);
    }

    private function stageCancel(EntityUuid $uuid): void
    {
        $stageCollection = $this->stageQuery->getOpenByTask($uuid);
        foreach ($stageCollection as $stage) {
            $state = new TaskStateCanceled(
                uuid: $stage->uuid,
                message: new TaskStateMessage('Canceled'),
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
