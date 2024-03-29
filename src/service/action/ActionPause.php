<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service\action;

use Throwable;
use kuaukutsu\poc\task\dto\StageModelState;
use kuaukutsu\poc\task\dto\TaskModelState;
use kuaukutsu\poc\task\handler\TaskFactory;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\state\TaskStatePaused;
use kuaukutsu\poc\task\state\TaskStateError;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\EntityTask;

final readonly class ActionPause implements TaskAction
{
    public function __construct(
        private StageCommand $stageCommand,
        private TaskCommand $taskCommand,
        private TaskFactory $factory,
        private TransitionState $transition,
    ) {
    }

    public function execute(EntityTask $task, ?TaskStateInterface $state = null): EntityTask
    {
        if ($task->isPaused() || $task->isPromised() || $task->isWaiting()) {
            return $task;
        }

        $state ??= new TaskStatePaused(
            message: new TaskStateMessage('Task Paused.'),
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
