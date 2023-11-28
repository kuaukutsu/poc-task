<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service\action;

use kuaukutsu\poc\task\dto\TaskState;
use kuaukutsu\poc\task\handler\TaskFactory;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\state\TaskStateRunning;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\EntityTask;

final class ActionRun implements TaskAction
{
    public function __construct(
        private readonly TaskCommand $command,
        private readonly TaskFactory $factory,
        private readonly TransitionState $transition,
    ) {
    }

    public function execute(EntityTask $task, ?TaskStateInterface $state = null): EntityTask
    {
        $state ??= new TaskStateRunning(
            uuid: $task->getUuid(),
            message: new TaskStateMessage('Runned'),
            flag: $task->getFlag(),
        );

        $this->transition->canAccessTransitionState(
            $task->getUuid(),
            $task->getFlag(),
            $state->getFlag()->toValue(),
        );

        $model = $this->command->state(
            new EntityUuid($task->getUuid()),
            new TaskState($state),
        );

        return $this->factory->create($model);
    }
}
