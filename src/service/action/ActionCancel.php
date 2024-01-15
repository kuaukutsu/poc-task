<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service\action;

use Throwable;
use kuaukutsu\poc\task\dto\StageModelState;
use kuaukutsu\poc\task\dto\TaskModelState;
use kuaukutsu\poc\task\handler\TaskFactory;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\state\TaskStateError;
use kuaukutsu\poc\task\state\TaskStateCanceled;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\EntityTask;

final class ActionCancel implements TaskAction
{
    public function __construct(
        private readonly StageCommand $stageCommand,
        private readonly TaskCommand $taskCommand,
        private readonly TaskFactory $factory,
        private readonly TransitionState $transition,
    ) {
    }

    public function execute(EntityTask $task, ?TaskStateInterface $state = null): EntityTask
    {
        if ($task->isFinished()) {
            return $task;
        }

        $state ??= new TaskStateCanceled(
            message: new TaskStateMessage('Task Canceled.'),
            flag: $task->getFlag(),
        );

        $this->transition->canAccessTransitionState(
            $task->getUuid(),
            $task->getFlag(),
            $state->getFlag()->toValue(),
        );

        $uuid = new EntityUuid($task->getUuid());

        try {
            $this->stageCommand->stateByTask($uuid, new StageModelState($state));
        } catch (Throwable $e) {
            $state = new TaskStateError(
                message: new TaskStateMessage($e->getMessage(), $e->getTraceAsString()),
                flag: $task->getFlag(),
            );
        }

        return $this->factory->create(
            $this->taskCommand->state($uuid, new TaskModelState($state))
        );
    }
}
