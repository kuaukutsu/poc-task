<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service\action;

use kuaukutsu\poc\task\dto\TaskModelState;
use kuaukutsu\poc\task\handler\TaskFactory;
use kuaukutsu\poc\task\service\TaskCommand;
use kuaukutsu\poc\task\state\TaskStateInterface;
use kuaukutsu\poc\task\state\TaskStateReady;
use kuaukutsu\poc\task\EntityTask;
use kuaukutsu\poc\task\EntityUuid;

final class ActionReady implements TaskAction
{
    public function __construct(
        private readonly TaskCommand $command,
        private readonly TaskFactory $factory,
    ) {
    }

    public function execute(EntityTask $task, ?TaskStateInterface $state = null): EntityTask
    {
        $model = $this->command->state(
            new EntityUuid($task->getUuid()),
            new TaskModelState(
                new TaskStateReady()
            ),
        );

        return $this->factory->create($model);
    }
}
