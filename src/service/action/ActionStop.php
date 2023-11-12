<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service\action;

use kuaukutsu\poc\task\dto\TaskModel;
use kuaukutsu\poc\task\handler\TaskFactory;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\state\TaskStateSkip;
use kuaukutsu\poc\task\EntityUuid;
use kuaukutsu\poc\task\TaskInterface;

final class ActionStop implements TaskAction
{
    public function __construct(
        private readonly TaskCommand $command,
        private readonly TaskFactory $factory,
    ) {
    }

    public function execute(TaskInterface $task): TaskInterface
    {
        // @fixme: Исправить на верное состояние.
        $state = new TaskStateSkip(
            uuid: $task->getUuid(),
            message: new TaskStateMessage('Stopped'),
            flag: $task->getFlag(),
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
