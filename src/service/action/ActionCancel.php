<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service\action;

use Throwable;
use kuaukutsu\poc\task\dto\StageModelState;
use kuaukutsu\poc\task\dto\TaskModelState;
use kuaukutsu\poc\task\handler\TaskFactory;
use kuaukutsu\poc\task\handler\TaskFinallyHandler;
use kuaukutsu\poc\task\service\StageCommand;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\state\TaskStateError;
use kuaukutsu\poc\task\state\TaskStateCanceled;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\EntityTask;

final readonly class ActionCancel implements TaskAction
{
    public function __construct(
        private StageCommand $stageCommand,
        private TaskCommand $taskCommand,
        private TaskFactory $factory,
        private TaskFinallyHandler $finallyHandler,
        private TransitionState $transition,
    ) {
    }

    public function execute(EntityTask $task, ?TaskStateInterface $state = null): EntityTask
    {
        if ($task->isFinished()) {
            return $task;
        }

        $state = new TaskStateCanceled(
            message: $state?->getMessage() ?? new TaskStateMessage('Canceled.'),
            flag: $task->getFlag(),
        );

        $this->transition->canAccessTransitionState(
            $task->getUuid(),
            $task->getFlag(),
            $state->getFlag()->toValue(),
        );

        $uuid = new EntityUuid($task->getUuid());
        $isRoot = $task->isPromised() === false;

        try {
            $this->stageCommand->stateByTask($uuid, new StageModelState($state));
        } catch (Throwable $e) {
            $state = new TaskStateError(
                message: new TaskStateMessage($e->getMessage(), $e->getTraceAsString()),
                flag: $task->getFlag(),
            );
        }

        $task = $this->factory->create(
            $this->taskCommand->state($uuid, new TaskModelState($state))
        );

        if ($isRoot) {
            $this->finallyHandler->handle(
                $task->getUuid(),
                $task->getOptions(),
                $task->getState(),
            );
        }

        return $task;
    }
}
