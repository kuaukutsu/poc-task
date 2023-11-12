<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service\action;

use kuaukutsu\poc\task\dto\TaskModel;
use kuaukutsu\poc\task\handler\TaskFactory;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\state\TaskStateRunning;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\TaskInterface;

final class ActionResume implements TaskAction
{
    public function __construct(
        private readonly TaskCommand $command,
        private readonly TaskFactory $factory,
    ) {
    }

    public function execute(TaskInterface $task): TaskInterface
    {
        $state = new TaskStateRunning(
            uuid: $task->getUuid(),
            message: new TaskStateMessage('Resume'),
            flag: $task->copyFlag()->unsetPaused()->toFlag(),
        );

        $model = $this->command->update(
            new EntityUuid($task->getUuid()),
            TaskModel::hydrate(
                [
                    'flag' => $state->getFlag(),
                    'state' => serialize($state),
                ]
            ),
        );

        return $this->factory->create($model);
    }
}
