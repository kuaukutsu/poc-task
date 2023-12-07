<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service\action;

use Throwable;
use kuaukutsu\poc\task\dto\StageModelState;
use kuaukutsu\poc\task\dto\TaskModelState;
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
            message: new TaskStateMessage('Canceled'),
            flag: $task->getFlag(),
        );

        $this->transition->canAccessTransitionState(
            $task->getUuid(),
            $task->getFlag(),
            $state->getFlag()->toValue(),
        );

        $model = $this->taskCommand->state(
            new EntityUuid($task->getUuid()),
            new TaskModelState($state),
        );

        $this->stageCancel($uuid);

        return $this->factory->create($model);
    }

    private function stageCancel(EntityUuid $uuid): void
    {
        foreach ($this->stageQuery->iterableOpenByTask($uuid) as $stage) {
            $state = new TaskStateCanceled(
                message: new TaskStateMessage('Canceled'),
                flag: $stage->flag,
            );

            try {
                $this->stageCommand->state(
                    new EntityUuid($stage->uuid),
                    new StageModelState($state),
                );
            } catch (Throwable) {
            }
        }
    }
}
