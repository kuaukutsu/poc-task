<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\service;

use kuaukutsu\poc\task\EntityUuid;

final class TaskDestroyer
{
    public function __construct(
        private readonly TaskCommand $taskCommand,
        private readonly StageCommand $stageCommand,
    ) {
    }

    public function purge(EntityUuid $uuid): void
    {
        $this->stageCommand->removeByTask($uuid);
        $this->taskCommand->remove($uuid);
    }
}
