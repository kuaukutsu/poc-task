<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service\action;

use kuaukutsu\poc\task\dto\TaskModelState;
use kuaukutsu\poc\task\state\TaskStateMessage;
use kuaukutsu\poc\task\state\TaskStateTerminate;
use kuaukutsu\poc\task\service\TaskCommand;

final readonly class ActionTerminate
{
    public function __construct(private TaskCommand $command)
    {
    }

    /**
     * @param non-empty-string[] $indexUuid
     */
    public function execute(array $indexUuid, int $signal): void
    {
        $this->command->terminate(
            $indexUuid,
            new TaskModelState(
                new TaskStateTerminate(
                    new TaskStateMessage("[$signal] signal terminate.")
                )
            )
        );
    }
}
